const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const source = fs.readFileSync('public/clients/assets/js/custom-js.js', 'utf8');
const start = source.indexOf('    function updateSummary()');
const end = source.indexOf('    // Sự kiện tăng/giảm', start);
const values = { '#numAdults': 2, '#numChildren': 1 };
const texts = {};
let available = 10;
const context = {
    discountRate: 0.1, discount: 0, totalPrice: 0,
    getQuantityAvailable: () => available,
    renderPassengerFields: () => {},
    toastr: { error: () => {} },
    $: selector => ({
        length: 1,
        val: value => value === undefined ? values[selector] : (values[selector] = value),
        data: () => selector === '#numAdults' ? 1000000 : 500000,
        text: text => { texts[selector] = text; },
    }),
};
vm.createContext(context);
vm.runInContext(source.slice(start, end), context);
context.updateSummary();
assert.equal(context.totalPrice, 2250000);
values['#numAdults'] = 3;
context.updateSummary();
assert.equal(context.discount, 350000);
assert.equal(context.totalPrice, 3150000);
assert.equal(texts['.transfer-amount'], (3150000).toLocaleString() + ' VNĐ');
assert.equal(texts['.deposit-price'], (630000).toLocaleString() + ' VNĐ');
available = 2;
values['#numAdults'] = 8;
values['#numChildren'] = 1;
context.updateSummary();
assert.equal(values['#numAdults'], 2);
assert.equal(values['#numChildren'], 0);
assert.equal(context.totalPrice, 1800000);
console.log('PASS: discount follows guest count, transfer/deposit totals update, restored guests fit available seats.');
