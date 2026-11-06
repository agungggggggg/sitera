const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

function setup() {
    class Select { constructor(name, value) { this.name = name; this.value = value; } }
    const values = Object.fromEntries(['provinsi','kabupaten','kecamatan','desa','kelompok','nama_di','status'].map(name => [name,new Select(name,'selected')]));
    let callback;
    let submitted = 0;
    const form = { elements: { namedItem: name => values[name] }, addEventListener: (event,handler) => { callback = handler; }, requestSubmit: () => submitted++ };
    vm.runInNewContext(fs.readFileSync(path.join(__dirname,'../../public/sitera/filter-controls.js'),'utf8'), {
        document: { querySelectorAll: () => [form] }, HTMLSelectElement: Select,
    });
    return { values, change: name => callback({target:values[name]}), submissions: () => submitted };
}

test('province changes clear dependent filters and submit once while preserving unrelated status', () => {
    const fixture = setup();
    fixture.change('provinsi');
    for (const name of ['kabupaten','kecamatan','desa','kelompok','nama_di']) assert.equal(fixture.values[name].value,'');
    assert.equal(fixture.values.status.value,'selected');
    assert.equal(fixture.submissions(),1);
});

test('district changes preserve province and regency and clear descendants', () => {
    const fixture = setup();
    fixture.change('kecamatan');
    assert.equal(fixture.values.provinsi.value,'selected');
    assert.equal(fixture.values.kabupaten.value,'selected');
    assert.equal(fixture.values.desa.value,'');
    assert.equal(fixture.submissions(),1);
});

test('status selection does not trigger geographical cascade', () => {
    const fixture = setup();
    fixture.change('status');
    assert.equal(fixture.values.kabupaten.value,'selected');
    assert.equal(fixture.submissions(),0);
});
