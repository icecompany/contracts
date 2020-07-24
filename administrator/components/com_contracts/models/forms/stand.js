'use strict';
Joomla.submitbutton = function (task) {
    let form = document.querySelector('#adminForm');
    let valid = document.formvalidator.isValid(form);
    if (task === 'stand.cancel' || valid) {
        let fields = document.querySelectorAll("#adminForm input[type='text']");
        fields.forEach(function(elem) {
            elem.value = elem.value.trim();
            elem.value = elem.value.replace(/\s+/g, ' ');
        });
        Joomla.submitform(task, form);
    }
};

function asset(obj) {
    let csid = obj.dataset.csid;
    let id = obj.dataset.id;
    let asset = (!obj.checked) ? 0 : 1;
    let url = `index.php?option=com_contracts&task=parent.asset&id=${id}&csid=${csid}&asset=${asset}`;
    jQuery.getJSON(url, function (json) {

    })
}
