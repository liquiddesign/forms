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
