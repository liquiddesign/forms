document.addEventListener("DOMContentLoaded", function(event)
{
    if (typeof LiveForm !== 'undefined' || typeof Nette !== 'undefined') {
        var Forms = (typeof LiveForm === 'undefined') ? Nette : LiveForm;
        var old = Forms.addError;
        Forms.addError = function (el, message) {
            old.apply(this, arguments);
            el.form.querySelector("input[name=__MUTATION_SELECTOR][value=" + el.getAttribute('data-mutation') + "]").click();
        };
    }

    initForms();
});

function formChangeMutation(form, mutation)
{
    b = form.querySelectorAll("tr[data-mutation]");
    for (var j in b) if (b.hasOwnProperty(j)) {
        b[j].style.display = 'none';
    }

    b = form.querySelectorAll("tr[data-mutation='" + mutation + "']");
    for (var j in b) if (b.hasOwnProperty(j)) {
        b[j].style.display = 'table-row';
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

function formIsMutationsActive(form, mutation) {
    let mutationTranslated = form.querySelector("input[name=__MUTATION_TRANSLATED\\["+ mutation +"\\]]");

    if (mutationTranslated) {
        return mutationTranslated.checked;
    }
}

function formGetAvailbleMutations(form) {
    let mutations = [];
    b = form.querySelectorAll("input[name=__MUTATION_SELECTOR]");

    for (var j in b) if (b.hasOwnProperty(j)) {
        mutations.push(b[j].value);
    }

    return mutations;
}

function initForms() {
    b = document.querySelectorAll("form");

    for (var j in b) if (b.hasOwnProperty(j)) {
       var mutation = formGetMutation(b[j]);
       if (mutation !== null) {
           formDisableMutation(b[j], mutation);
       }
    }
}


function formDisableMutation(form, mutation) {

    let isActive = formIsMutationsActive(form, mutation);
    console.log(isActive);

    b = form.querySelectorAll("tr[data-mutation='" + mutation + "']");
    for (var j in b) if (b.hasOwnProperty(j)) {

        if (!b[j].querySelector("input[name=__MUTATION_TRANSLATED\\["+ mutation +"\\]]")) {
            b[j].style.display = !isActive ? 'none' : 'table-row';

            var nodes =  b[j].getElementsByTagName('*');
            for(var i = 0; i < nodes.length; i++){
                nodes[i].disabled = !isActive;
            }
        }
    }
}