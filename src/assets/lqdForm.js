document.addEventListener("DOMContentLoaded", function(event)
{
    if (typeof LiveForm !== 'undefined' || typeof Nette !== 'undefined') {
        var Forms = (typeof LiveForm === 'undefined') ? Nette : LiveForm;
        var old = Forms.addError;
        Forms.addError = function (el, message) {
            old.apply(this, arguments);

            var mutation = el.getAttribute('data-mutation');

            if (mutation === null || !el.form) {
                return;
            }

            var mutationSelector = el.form.querySelector("input[name=__MUTATION_SELECTOR][value=" + mutation + "]");

            if (mutationSelector) {
                mutationSelector.click();
            }
        };
    }

    // nette/forms 3.x (PHP) uz neexportuje marker {op: 'optional'}, ktery netteForms 2.4
    // potrebuje k preskoceni prazdnych nepovinnych poli. Bez nej klientska validace
    // :float/:integer na prazdnem nepovinnem poli chybne blokuje submit ("Zadejte platne cislo").
    // Dopocitame emptyOptional stejne, jako to dela netteForms 3.x (emptyOptional ??= !:filled).
    if (typeof Nette !== 'undefined' && Nette.version === '2.4' && typeof Nette.validateControl === 'function') {
        var oldValidateControl = Nette.validateControl;
        Nette.validateControl = function (elem, rules, onlyCheck, value, emptyOptional) {
            elem = elem.tagName ? elem : elem[0]; // RadioNodeList

            if (emptyOptional === undefined) {
                var curValue = value === undefined ? {value: Nette.getEffectiveValue(elem)} : value;
                emptyOptional = !Nette.validateRule(elem, ':filled', null, curValue);
            }

            return oldValidateControl.call(this, elem, rules, onlyCheck, value, emptyOptional);
        };
    }
});

function formChangeMutation(form, mutation)
{
    b = form.querySelectorAll("tr[data-mutation]");
    for (var j in b) if (b.hasOwnProperty(j)) {
        b[j].classList.add("inactive");
    }

    b = form.querySelectorAll("tr[data-mutation='" + mutation + "']");
    for (var j in b) if (b.hasOwnProperty(j)) {
        b[j].classList.remove("inactive");
    }

    formDisableMutation(form, mutation);
}

function formGetMutation(form) {
    let mutationSelector = form.querySelector("input[name=__MUTATION_SELECTOR]:checked");

    if (mutationSelector) {
        return mutationSelector.value;
    }

    return null;
}

function formIsMutationsActive(form, mutation, translatorName = 'active') {
    let mutationTranslated = form.querySelector("input[name=" + translatorName + "\\["+ mutation +"\\]]");

    if (mutationTranslated) {
        return mutationTranslated.checked;
    }

    return null;
}

function formGetAvailbleMutations(form) {
    let mutations = [];
    b = form.querySelectorAll("input[name=__MUTATION_SELECTOR]");

    for (var j in b) if (b.hasOwnProperty(j)) {
        mutations.push(b[j].value);
    }

    return mutations;
}

function formDisableMutation(form, mutation, translatorName = 'active') {

    let isActive = formIsMutationsActive(form, mutation, translatorName);

    b = form.querySelectorAll("tr[data-mutation='" + mutation + "']");

    for (var j in b) if (b.hasOwnProperty(j)) {
        if (!b[j].querySelector("input[name=active\\["+ mutation +"\\]]") && isActive !== null) {
            var nodes =  b[j].getElementsByTagName('*');
            for(var i = 0; i < nodes.length; i++){
                nodes[i].disabled = !isActive;
            }
        }
    }

    b = form.querySelectorAll("fieldset");

    for (var j in b) if (b.hasOwnProperty(j)) {
        if (j != 0 && j != b.length - 1) {
            b[j].style.display = !isActive && isActive !== null ? 'none' : 'block';
        }
    }
}
