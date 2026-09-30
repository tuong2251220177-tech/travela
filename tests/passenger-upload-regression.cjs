const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const source = fs.readFileSync('public/clients/assets/js/custom-js.js', 'utf8');
const start = source.indexOf('    let passengerUpload = null;');
const end = source.indexOf('    function updateSummary()', start);
let change, request;
const elements = new Map();
function $(selector) {
    if (!elements.has(selector)) {
        elements.set(selector, {
            value: selector.includes("_token") ? 'test-csrf' : '1',
            val(value) { if (value === undefined) return this.value; this.value = value; return this; },
            empty() { return this; }, text(value) { this.message = value; return this; },
            data() { return '/booking/passenger-preview'; },
            on(event, handler) { change = handler; },
        });
    }
    return elements.get(selector);
}
$.ajax = options => { request = options; return { abort() {} }; };
class FormData { append() {} }
vm.runInNewContext(source.slice(start, end), { $, FormData });
change.call({ files: [{ name: 'passengers.xlsx', size: 5000 }] });
assert.ok(request, 'Selecting a file must reach AJAX without a ReferenceError');
assert.equal(request.headers['X-CSRF-TOKEN'], 'test-csrf');
assert.equal(request.url, '/booking/passenger-preview');
assert.equal(request.type, 'POST');
request.error({ responseJSON: { errors: { passenger_file: ['Dòng 4: Email không hợp lệ.'] } } }, 'error');
assert.equal($('#passenger-file-status').message, 'Dòng 4: Email không hợp lệ.');
assert.equal($('#passenger-file').value, '');
console.log('PASS: selecting Excel sends the request with the form CSRF token and displays validation errors.');
