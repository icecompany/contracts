'use strict';
let url_exhibitors = "index.php?option=com_companies&task=companies.execute&format=json";

let Company = {
    searchByName: function (title) {
        jQuery.getJSON(`${url_exhibitors}&search=${title}`, function (json) {
            UI.Fields.par.elem.empty();
            jQuery.each(json.data, function (idx, obj) {
                UI.Fields.par.elem.append(`<option value="${obj.id}">${obj.title} (${obj.city})</option>`);
                UI.Fields.unlock(UI.Fields.par);
                UI.Fields.par.inp.value = title;
            })
        })
    },
    load: function () {
        let id = document.querySelector("#jform_payer_id").value;
        let value = document.querySelector("#jform_payer_title").value;
        if (id !== '' && value !== '') {
            UI.Fields.par.elem.append(`<option value="${id}" selected>${value}</option>`);
            UI.Fields.unlock(UI.Fields.par);
        }
    },
};

let UI = {
    Fields: {
        par: {
            chzn: '',
            inp: '',
            elem: ''
        },
        unlock: function (e) {
            e.elem.chosen({width: "95%"});
            e.elem.trigger("liszt:updated");
            e.chzn.classList.remove("chzn-container-single-nosearch");
            e.inp.removeAttribute('readonly');
        }
    },
};

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
    let columnID = document.querySelector("#jform_columnID");
    let description = document.querySelector("#jform_description");
    if (val < 1) {
        value.value = (0).toLocaleString('ru-RU', {style:'currency', currency:currency});
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
            if (columnID.value === '2') {
                price_rub *= parseFloat(text.data.column_2).toFixed(2);
                price_usd *= parseFloat(text.data.column_2).toFixed(2);
                price_eur *= parseFloat(text.data.column_2).toFixed(2);
            }
            if (columnID.value === '3') {
                price_rub *= parseFloat(text.data.column_3).toFixed(2);
                price_usd *= parseFloat(text.data.column_3).toFixed(2);
                price_eur *= parseFloat(text.data.column_3).toFixed(2);
            }
            if (currency === 'rub') {
                cost_price = price_rub;
                if (parseFloat(cost_price) === 0) {
                    cost_price = parseFloat(cost.value);
                }
                else {
                    jQuery(cost).val(cost_price.toLocaleString('ru-RU', {style:'currency', currency:currency})).trigger("liszt:updated");
                }
            }
            if (currency === 'usd') {
                cost_price = price_usd;
                if (parseFloat(cost_price) === 0) {
                    cost_price = parseFloat(cost.value);
                }
                else {
                    jQuery(cost).val(cost_price.toLocaleString('ru-RU', {style:'currency', currency:currency})).trigger("liszt:updated");
                }
            }
            if (currency === 'eur') {
                cost_price = price_eur;
                if (parseFloat(cost_price) === 0) {
                    cost_price = parseFloat(cost.value);
                }
                else {
                    jQuery(cost).val(cost_price.toLocaleString('ru-RU', {style:'currency', currency:currency})).trigger("liszt:updated");
                }
            }
            if (text.data.type === 'square' || text.data.type === 'electric' || text.data.type === 'internet' || text.data.type === 'multimedia' || text.data.type === 'water' || text.data.type === 'cleaning') {
                stand.removeAttribute('disabled');
                jQuery(stand).trigger("liszt:updated");
            }
            else {
                stand.setAttribute('disabled', true);
                jQuery(stand).trigger("liszt:updated");
            }
            if (text.data.type === 'fine' || text.data.type === 'transfer') {
                cost.removeAttribute('readonly');
                value.value = 1;
                columnID.value = 1;
            }
            else {
                cost.setAttribute('readonly', true);
            }
            if (text.data.type === 'technical') {
                cost.removeAttribute('readonly');
                description.removeAttribute('readonly');
            }
            else {
                description.setAttribute('readonly', true);
            }
            getAmount();
            getUnit(text.data.unit_2_ID);
        })
        .catch(function (error) {
            console.log('Request failed', error);
            cost_price = 0;
        });
    console.log();
}

function getAmount() {
    let value = document.querySelector("#jform_value");
    let value2 = document.querySelector("#jform_value2");
    let factor_field = document.querySelector("#jform_factor");
    let markup = document.querySelector("#jform_markup");
    let contract_new_amount = document.querySelector("#jform_contract_new_amount");
    if (value.value === '' || factor_field.value === '' || markup.value === '') return;
    let factor = parseFloat((1 - (factor_field.value / 100)).toFixed(2));
    let a = parseFloat(cost_price) * parseFloat(value.value); //Цена без скидок и наценок
    let b = parseFloat(cost_price) * parseFloat(value.value) * factor; //Цена со скидкой
    let c = a - b; //Скидка
    let d = a * parseFloat(markup.value); //Цена с наценкой
    let e = parseFloat((value2.value !== '' && value2.value !== '0') ? value2.value : 1);
    let amount = ((d - c) * e).toFixed(2);
    document.querySelector("#jform_amount").value = parseFloat(amount).toLocaleString('ru-RU', {style:'currency', currency:currency});
    contract_new_amount.value = (old_amount - old_price_value + parseFloat(amount)).toLocaleString('ru-RU', {style:'currency', currency:currency});
}

function setValue() {
    let field = document.getElementById("jform_contractStandID");
    let square = field.options[field.selectedIndex].getAttribute('data-square');
    if (square === null) square = 0;
    document.querySelector("#jform_value").value = square;
    getCost();
}

function getUnit(id) {
    let url = `index.php?option=com_prices&task=unit.execute&id=${id}&format=json`;
    fetch(url)
        .then((response) => {
            return response.json();
        }, (error) => {
            console.log(`Error unit get: ${error}`);
        })
        .then((response) => {
            let field = document.querySelector("#jform_value2-lbl");
            field.innerText = response.data.title;
        });
}

window.onload = function () {
    UI.Fields.par.elem = jQuery("#jform_payerID");
    UI.Fields.par.chzn = document.querySelector("#jform_payerID_chzn");
    UI.Fields.par.inp = document.querySelector("#jform_payerID_chzn .chzn-drop .chzn-search input");
    UI.Fields.unlock(UI.Fields.par);
    Company.load();
    jQuery(UI.Fields.par.inp).autocomplete({source: function () {
            let val = UI.Fields.par.inp.value;
            if (val.length < 3) return;
            Company.searchByName(val);
        }
    });
    let list = document.querySelectorAll("#jform_factor option");
    document.querySelector("#jform_contract_old_amount").value = (old_amount).toLocaleString('ru-RU', {style:'currency', currency:currency});
    document.querySelector("#jform_contract_new_amount").value = (old_amount).toLocaleString('ru-RU', {style:'currency', currency:currency});
    document.querySelector("#jform_amount").value = parseFloat(document.querySelector("#jform_amount").value).toLocaleString('ru-RU', {style:'currency', currency:currency});
    document.querySelector("#jform_cost").value = parseFloat(document.querySelector("#jform_cost").value).toLocaleString('ru-RU', {style:'currency', currency:currency});
    if (document.querySelector("#jform_old_amount") !== null) {
        document.querySelector("#jform_old_amount").value = parseFloat(document.querySelector("#jform_old_amount").value).toLocaleString('ru-RU', {
            style: 'currency',
            currency: currency
        });
    }
    for(let elem of list) elem.innerText += "%";
    jQuery("#jform_factor").trigger("liszt:updated");
}