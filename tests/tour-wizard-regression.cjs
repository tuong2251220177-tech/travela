const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const source = fs.readFileSync('public/admin/assets/js/custom-js.js', 'utf8');
const start = source.indexOf('    document.addEventListener("click", function (event) {');
const end = source.indexOf('    }, true);', start) + '    }, true);'.length;
assert.ok(start >= 0 && end > start);
let handler, submissions = 0;
vm.runInNewContext(source.slice(start, end), {
    document: { addEventListener: (_, callback) => { handler = callback; } },
    $: () => ({ smartWizard: () => 2 }),
    submitTourTimeline: () => { submissions++; },
});
function click(isAddPage, kind) {
    let intercepted = false;
    const button = { classList: { contains: name => name === kind } };
    handler({
        target: { closest: selector => selector.split(',').some(part =>
            part.includes('.' + kind) && (!part.includes('.add-tours') || isAddPage)) ? button : null },
        preventDefault: () => { intercepted = true; },
        stopImmediatePropagation: () => { intercepted = true; },
    });
    return intercepted;
}
assert.equal(click(false, 'buttonFinish'), false, 'Edit Finish must reach its own save handler');
assert.equal(click(false, 'buttonNext'), false, 'Edit Next must stay inside the edit wizard');
assert.equal(submissions, 0, 'Editing must not submit the new-tour form');
assert.equal(click(true, 'buttonFinish'), true);
assert.equal(submissions, 1, 'Creating a tour still submits its own timeline');
console.log('PASS: edit wizard clicks are not intercepted by the new-tour handler.');
const buildSource = fs.readFileSync('public/admin/build/js/custom.js', 'utf8');
const dayExpression = buildSource.match(/var daysDifference = ([^;]+);/)[1];
assert.equal(vm.runInNewContext(dayExpression, { timeDifference: Date.parse('2026-10-05') - Date.parse('2026-10-03') }), 3);
assert.equal(vm.runInNewContext(dayExpression, { timeDifference: 0 }), 1);
const limitExpression = source.match(/if \((\$\("\.add-tours #step-3 \.timeline-entry"\)\.length >= maxTimelineDays)\)/)[1];
for (const [rows, blocked] of [[2, false], [3, true], [2, false]]) {
    assert.equal(vm.runInNewContext(limitExpression, { $: () => ({ length: rows }), maxTimelineDays: 3, timelineCounter: 5 }), blocked);
}
console.log('PASS: three calendar days allow three entries, including adding again after removal.');
