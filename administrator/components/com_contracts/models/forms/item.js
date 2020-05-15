'use strict';
Joomla.submitbutton = function (task) {
    let form = document.querySelector('#adminForm');
    let valid = document.formvalidator.isValid(form);
    if (task === 'item.cancel' || valid) {
        let fields = document.querySelectorAll("#adminForm input[type='text']");
        fields.forEach(function(elem) {
            elem.value = elem.value.trim();
            elem.value = elem.value.replace(/\s+/g, ' ');
        });
        Joomla.submitform(task, form);
    }
};

function getCost() {
    let section = document.querySelector("#jform_itemID");
    let cost = document.querySelector("#jform_cost");
    let stand = document.querySelector("#jform_contractStandID");
    let val = jQuery(section).val();
    let value = document.querySelector("#jform_value");
    if (val < 1) {
        value.value = 0;
        value.setAttribute('readonly', true);
        stand.setAttribute('disabled', true);
        jQuery(stand).trigger("liszt:updated");
        cost.value = 0;
        return false;
    }
    fetch(`index.php?option=com_prices&task=item.execute&id=${val}&format=json`)
        .then(function (response) {
            return response.json();
        })
        .then(function (text) {
            let price_rub = text.data.price_rub;
            let price_usd = text.data.price_usd;
            let price_eur = text.data.price_eur;
            if (currency === 'rub') jQuery(cost).val(price_rub).trigger("liszt:updated");
            if (currency === 'usd') jQuery(cost).val(price_usd).trigger("liszt:updated");
            if (currency === 'eur') jQuery(cost).val(price_eur).trigger("liszt:updated");
            if (text.data.type === 'square' || text.data.type === 'electric' || text.data.type === 'internet' || text.data.type === 'multimedia' || text.data.type === 'water' || text.data.type === 'cleaning') {
                stand.removeAttribute('disabled');
                jQuery(stand).trigger("liszt:updated");
            }
            else {
                stand.setAttribute('disabled', true);
                jQuery(stand).trigger("liszt:updated");
            }
            value.removeAttribute('readonly');
        })
        .catch(function (error) {
            console.log('Request failed', error);
        });
    console.log();
}

function getAmount() {
    getCost();
    let cost = document.querySelector("#jform_cost");
    let value = document.querySelector("#jform_value");
    let factor_field = document.querySelector("#jform_factor");
    let markup = document.querySelector("#jform_markup");
    if (cost.value === '' || value.value === '' || factor_field.value === '' || markup.value === '') return;
    let factor = parseFloat((1 - (factor_field.value / 100)).toFixed(2));
    let amount = (parseFloat(cost.value) * parseFloat(value.value) * factor * parseFloat(markup.value)).toFixed(2);
    document.querySelector("#jform_amount").value = amount.toString();
}

window.onload = function () {
    let list = document.querySelectorAll("#jform_factor option");
    for(let elem of list) elem.innerText += "%";
    jQuery("#jform_factor").trigger("liszt:updated");
}