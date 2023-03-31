function addInvoiceSnackBar(data) {
    if (data.eventType === undefined || data.eventMessage === undefined) {
        return;
    }

    const snackBarTemplate = document.getElementById('snackbar-template');
    let snackBar = document.createElement('div');
    snackBar.innerHTML = snackBarTemplate.innerHTML;
    snackBar.classList.add(data.eventType);

    if (data.eventType === 'error') {
        let path = snackBar.querySelector('path');
        path.attributes.getNamedItem('d').value = 'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
        let parent = path.parentElement;
        parent.classList.remove('text-green-400');
        parent.classList.add('text-red-400');
    }

    snackBar.querySelector('p').innerHTML = data.eventMessage;
    snackBar.getElementsByClassName('snackbar-close')[0].onclick = function () {
        snackBar.remove();
    }

    const snackBars = document.getElementById('snackbars');
    snackBars.prepend(snackBar);

    setTimeout(function () {
        snackBar.remove()
    }, 5000)
}
