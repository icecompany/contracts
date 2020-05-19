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

let cost_price = parseFloat('0');

function getCost() {
    let itemID = document.querySelector("#jform_itemID");
    let cost = document.querySelector("#jform_cost");
    let stand = document.querySelector("#jform_contractStandID");
    let val = jQuery(itemID).val();
    let value = document.querySelector("#jform_value");
    if (val < 1) {
        value.value = (0).toLocaleString('ru-RU', {style:'currency', currency:currency});
        value.setAttribute('readonly', true);
        stand.setAttribute('disabled', true);
        jQuery(stand).trigger("liszt:updated");
        cost.value = (0).toLocaleString('ru-RU', {style:'currency', currency:currency});
        cost_price = 0;
        return false;
    }
    fetch(`index.php?option=com_prices&task=item.execute&id=${val}&format=json`)
        .then(function (response) {
            return response.json();
        })
        .then(function (text) {
            let price_rub = parseFloat(text.data.price_rub);
            let price_usd = parseFloat(text.data.price_usd);
            let price_eur = parseFloat(text.data.price_eur);
            if (currency === 'rub') {
                cost_price = price_rub;
                jQuery(cost).val(price_rub.toLocaleString('ru-RU', {style:'currency', currency:currency})).trigger("liszt:updated");
            }
            if (currency === 'usd') {
                cost_price = price_usd;
                jQuery(cost).val(price_usd.toLocaleString('ru-RU', {style:'currency', currency:currency})).trigger("liszt:updated");
            }
            if (currency === 'eur') {
                cost_price = price_eur;
                jQuery(cost).val(price_eur.toLocaleString('ru-RU', {style:'currency', currency:currency})).trigger("liszt:updated");
            }
            if (text.data.type === 'square' || text.data.type === 'electric' || text.data.type === 'internet' || text.data.type === 'multimedia' || text.data.type === 'water' || text.data.type === 'cleaning') {
                stand.removeAttribute('disabled');
                jQuery(stand).trigger("liszt:updated");
            }
            else {
                stand.setAttribute('disabled', true);
                jQuery(stand).trigger("liszt:updated");
            }
            value.removeAttribute('readonly');
            getAmount();
        })
        .catch(function (error) {
            console.log('Request failed', error);
            cost_price = 0;
        });
    console.log();
}

function getAmount() {
    let value = document.querySelector("#jform_value");
    let factor_field = document.querySelector("#jform_factor");
    let markup = document.querySelector("#jform_markup");
    let contract_new_amount = document.querySelector("#jform_contract_new_amount");
    if (value.value === '' || factor_field.value === '' || markup.value === '') return;
    let factor = parseFloat((1 - (factor_field.value / 100)).toFixed(2));
    let a = parseFloat(cost_price) * parseFloat(value.value); //Цена без скидок и наценок
    let b = parseFloat(cost_price) * parseFloat(value.value) * factor; //Цена со скидкой
    let c = a - b; //Скидка
    let d = a * parseFloat(markup.value); //Цена с наценкой
    let amount = (d - c).toFixed(2);
    console.log(cost_price, a, b, c, d, amount);
    document.querySelector("#jform_amount").value = parseFloat(amount).toLocaleString('ru-RU', {style:'currency', currency:currency});
    contract_new_amount.value = (old_amount + parseFloat(amount)).toLocaleString('ru-RU', {style:'currency', currency:currency});
}

window.onload = function () {
    let list = document.querySelectorAll("#jform_factor option");
    document.querySelector("#jform_contract_old_amount").value = (old_amount).toLocaleString('ru-RU', {style:'currency', currency:currency});
    for(let elem of list) elem.innerText += "%";
    jQuery("#jform_factor").trigger("liszt:updated");
}