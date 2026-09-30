const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const source = fs.readFileSync('public/clients/assets/js/custom-js.js', 'utf8');
const elements = new Map();
function $(selector) {
    if (typeof selector === 'object') return selector;
    if (!elements.has(selector)) elements.set(selector, {
        length: 1, value: '', props: {}, attributes: {}, handlers: {},
        val(v) { if (v === undefined) return this.value; this.value = v; return this; },
        data(k, v) { if (v === undefined) return this.attributes[k]; this.attributes[k] = v; return this; },
        prop(k, v) { if (v === undefined) return this.props[k]; this.props[k] = v; return this; },
        text(v) { if (v === undefined) return this.label; this.label = v; return this; },
        css() { return this; }, toggleClass() { return this; },
        each(fn) { if (selector === '.choose-coupon' || selector === '.coupon-suggestion' || selector === 'input[name="transport-choice"]') fn.call(this); return this; },
        find(s) { return $(s); }, on(e, fn) { this.handlers[e] = fn; return this; },
        trigger(e) { this.handlers[e]?.call(this, { preventDefault() {} }); return this; },
    });
    return elements.get(selector);
}
const requests = [];
$.ajax = options => { requests.push(options); return { abort() {} }; };
$('#numAdults').val(1).data('price-adults', 1000000);
$('#numChildren').val(0).data('price-children', 500000);
$('.choose-coupon').data('code', 'SAVE');
$('.coupon-suggestion').data('min-total', 0);
$('.order-coupon').data('url', '/preview').data('clear-url', '/clear');
const context = vm.createContext({ $, toastr: { info() {}, success() {}, error() {} },
    getQuantityAvailable: () => 30, renderPassengerFields() {} });
const state = source.slice(source.indexOf('    let discount = 0;'), source.indexOf('    function getQuantityAvailable()'));
const summary = source.slice(source.indexOf('    function updateSummary()'), source.indexOf('    // Sự kiện tăng/giảm số lượng'));
const handlers = source.slice(source.indexOf('    // Áp dụng mã giảm giá'), source.indexOf('    // Áp dụng mã giảm giá') + source.slice(source.indexOf('    // Áp dụng mã giảm giá')).indexOf('\n    });', source.slice(source.indexOf('    // Áp dụng mã giảm giá')).indexOf('$(".btn-coupon").on')) + 8);
vm.runInContext(state + summary + handlers, context);
vm.runInContext('updateSummary()', context);
$('.order-coupon input').val('SAVE');
$('.btn-coupon').trigger('click').trigger('click');
$('.choose-coupon').trigger('click');
assert.equal(requests.length, 1, 'Repeated clicks must send only one request');
requests[0].success({ code: 'SAVE', discount: 100000 });
requests[0].complete();
for (let i = 0; i < 10; i++) vm.runInContext('updateSummary()', context);
assert.equal($('.totalPrice').val(), 900000, 'Discount must never accumulate');
assert.equal($('.choose-coupon').label, 'Hủy mã');
$('.choose-coupon').trigger('click');
assert.equal(requests[1].url, '/clear', 'Suggested coupon cancel must clear server state');
$('.btn-coupon').trigger('click');
assert.equal(requests.length, 2, 'Cancel must also resist repeated clicks');
requests[1].error(); requests[1].complete();
assert.equal($('#applied-coupon').val(), 'SAVE', 'Failed cancellation must retain the coupon');
assert.equal($('.totalPrice').val(), 900000);
$('.btn-coupon').trigger('click');
requests[2].success(); requests[2].complete();
assert.equal($('#applied-coupon').val(), '');
assert.equal($('.totalPrice').val(), 1000000);
$('.coupon-suggestion').data('min-total', 2000000);
vm.runInContext('updateSummary()', context);
assert.equal($('.choose-coupon').prop('disabled'), true, 'Summary refresh must preserve eligibility');
console.log('PASS: single request, stable total, server cancellation, failed cancellation, coupon eligibility.');
$('#transport-option option:selected').data('fee', 200000).data('unit', 'person');
$('#numChildren').val(1);
vm.runInContext('updateSummary()', context);
assert.equal($('.totalPrice').val(), 1900000, 'Vehicle surcharge applies to every passenger');
$('#transport-option option:selected').data('unit', 'booking');
vm.runInContext('updateSummary()', context);
assert.equal($('.totalPrice').val(), 1700000, 'Per-booking surcharge is added only once');
$('#transport-option option:selected').data('fee', 0);
vm.runInContext('updateSummary()', context);
assert.equal($('.totalPrice').val(), 1500000, 'Switching back to standard restores the base price');
console.log('PASS: transport totals for per-person, per-booking and standard options.');
$('#payment-plan').val('full').trigger('change');
assert.equal($('#payment-now-amount').label, (1500000).toLocaleString('vi-VN') + ' VNĐ');
$('#payment-plan').val('deposit').trigger('change');
assert.equal($('#payment-now-amount').label, (300000).toLocaleString('vi-VN') + ' VNĐ');
console.log('PASS: full and deposit payment amounts.');
const vehicleCard = $('input[name="transport-choice"]');
vehicleCard.val('7').trigger('change');
assert.equal($('#transport-option').val(), '7', 'Selecting a card updates the submitted transport');
assert.equal(vehicleCard.prop('checked'), true);
$('#applied-coupon').val('SAVE');
vehicleCard.val('8').trigger('change');
assert.equal($('#transport-option').val(), '7', 'An active coupon prevents changing transport');
assert.equal(vehicleCard.prop('checked'), false, 'A blocked card must not remain visually selected');
$('#applied-coupon').val('');
vehicleCard.val('0').trigger('change');
assert.equal($('#transport-option').val(), '0', 'Standard transport can be selected again after clearing coupon');
assert.equal(vehicleCard.prop('checked'), true);
console.log('PASS: vehicle cards synchronize selection and respect active coupon restrictions.');
