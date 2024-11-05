
const addPhoneFormDeleteLink = (item, index) => {
    const removeFormButton = document.createElement('button');
    removeFormButton.innerText = "delete this number";
    removeFormButton.className = "btn btn-outline-danger"

    if (index) {
        item.innerHTML = item.innerHTML
            .replace(
                /Number #/g,
                "number #" + index
            );
    }
    item.append(removeFormButton);
    removeFormButton.addEventListener('click', (e) => {
        e.preventDefault();
        // remove the li for the tag form
        item.remove();
    });
}

const addFormToCollection = (e) => {
    const collectionHolder = document.querySelector('.' + e.currentTarget.dataset.collectionHolderClass);
    const item = document.createElement('div');
    item.innerHTML = collectionHolder
        .dataset
        .prototype
        .replace(
            /__name__/g,
            collectionHolder.dataset.index
        );
    collectionHolder.appendChild(item);
    collectionHolder.dataset.index++;
    addPhoneFormDeleteLink(item, collectionHolder.dataset.index);
};

$(document).ready(function () {
    document.querySelectorAll('div#account_phones div.mb-3')
        .forEach((phone, i) => {
            addPhoneFormDeleteLink(phone, i + 1)
        })
    document.querySelectorAll('.add_item_link')
        .forEach(btn => {
            btn.addEventListener("click", addFormToCollection)
        });
});
