const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

for (const failed of [false, true]) {
    test(`resend ${failed ? 'failure' : 'success'} keeps pagination and sends only once`, async () => {
        const view = fs.readFileSync(path.join(__dirname, '../../resources/views/pengaturan/histori/index.blade.php'), 'utf8');
        const script = view.match(/<script>([\s\S]*?)<\/script>/)[1].replace(/\{\{[\s\S]*?\}\}/g, 'test-url');
        const handlers = {};
        const reloads = [];
        const notices = [];
        let options;
        let requests = 0;
        const document = {};
        const form = {attr: () => '/histori-pesan/resend/123', serialize: () => '_token=fake'};
        const noop = {prop() { return this; }, submit() {}, text() {}, modal() {}, show() {}, data() { return ''; }};
        const table = {ajax: {reload: (...args) => reloads.push(args)}};
        function $(selector) {
            if (selector === document) return {ready: fn => fn()};
            if (selector === '#data') return {
                DataTable(config) { options = config; return table; },
                on(event, target, handler) { handlers[target] = handler; },
            };
            if (typeof selector === 'object') return {closest: () => form};
            return noop;
        }
        $.ajax = async () => {
            requests++;
            if (failed) throw {status: 422, responseJSON: {message: 'Provider rejected'}};
            return {message: 'Sent'};
        };
        const Swal = {fire: async notice => { notices.push(notice); return {isConfirmed: true}; }, showLoading() {}};
        vm.runInNewContext(script, {document, $, Swal});
        assert.equal(options.stateSave, true);
        const event = {preventDefault() {}};
        handlers['.btn-resend'].call({}, event);
        handlers['.btn-resend'].call({}, event);
        await new Promise(resolve => setImmediate(resolve));
        assert.equal(requests, 1);
        assert.deepEqual(reloads, [[null, false]]);
        assert.ok(notices.some(notice => notice.icon === (failed ? 'error' : 'success')));
    });
}
