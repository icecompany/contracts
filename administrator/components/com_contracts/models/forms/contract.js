'use strict';
let url_exhibitors = "index.php?option=com_companies&task=companies.execute&format=json";

let Company = {
    searchByName: function (title) {
        let companyID = document.querySelector("#jform_companyID").value;
        jQuery.getJSON(`${url_exhibitors}&search=${title}&not=${companyID}`, function (json) {
            UI.Fields.par.elem.empty();
            jQuery.each(json.data, function (idx, obj) {
                UI.Fields.par.elem.append(`<option value="${obj.id}">${obj.title} (${obj.city})</option>`);
                UI.Fields.unlock(UI.Fields.par);
                UI.Fields.par.inp.value = title;
            })
        })
    },
    load: function () {
        let id = document.querySelector("#jform_parent_id").value;
        let value = document.querySelector("#jform_parent_title").value;
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

window.onload = function () {
    UI.Fields.par.elem = jQuery("#jform_parentID");
    UI.Fields.par.chzn = document.querySelector("#jform_parentID_chzn");
    UI.Fields.par.inp = document.querySelector("#jform_parentID_chzn .chzn-drop .chzn-search input");
    UI.Fields.unlock(UI.Fields.par);
    Company.load();
    jQuery(UI.Fields.par.inp).autocomplete({source: function () {
            let val = UI.Fields.par.inp.value;
            if (val.length < 3) return;
            Company.searchByName(val);
        }
    });

    //Сохранение активной вкладки на странице
    jQuery('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        // save the latest tab; use cookies if you like 'em better:
        localStorage.setItem('lastTab', jQuery(this).attr('href'));
    });
    var lastTab = localStorage.getItem('lastTab');
    if (lastTab) {
        jQuery('[href="' + lastTab + '"]').tab('show');
    }
};

Joomla.submitbutton = function (task) {
    let form = document.querySelector('#adminForm');
    let valid = document.formvalidator.isValid(form);
    if (task === 'contract.cancel' || valid) {
        let fields = document.querySelectorAll("#adminForm input[type='text']");
        fields.forEach(function(elem) {
            elem.value = elem.value.trim();
            elem.value = elem.value.replace(/\s+/g, ' ');
        });
        Joomla.submitform(task, form);
    }
};

