import '../../public/bundles/datatables/js/datatables'
import DataTable from 'datatables.net-dt';
$(document).ready(function () {
    let table = $('#accounts');
    let config = table.data('settings');

    let promTable = table.initDataTables(config, {}).then(function (dt) {
        dt.on('draw', function () {
            $(document).on('click', '.account-delete', function () {
                $('#delete-modal-confirm').data('account-id', $(this).data('account-id'));
            })
        });
        return dt;
    });

    $(document).on('click', '#delete-modal-confirm', function () {
        let id = $(this).data('account-id');

        $.ajax({
            url: Routing.generate('api_account_delete', {id: id}),
            type: "DELETE",
            success: function (data) {
                console.log("success");
                console.log(data);
                $('#dt').DataTable().draw();
                notifyUser('Account deleted');
            },
            error: function (xhr) {
                console.log("error");
                console.log(xhr);

                notifyUser('Account not deleted');
            }
        });
    })

    document.querySelector('#search-global').addEventListener('keyup', () =>
        asyncFilterGlobal(promTable)
    );
    document.querySelectorAll('#account-search .column_filter').forEach((el) => {
        let columnIndex = el.getAttribute('data-column');
        el.addEventListener(el.type === 'text' ? 'keyup' : 'change', () =>
            asyncFilterColumn(promTable, columnIndex, el.value)
        );
    });

    document.querySelectorAll('#search-start-date, #search-end-date').forEach((el) => {
        let columnIndex = el.getAttribute('data-column');
        el.addEventListener('change', () => {
                asyncFilterGlobal(promTable);
            }
        );
    });
    document.querySelectorAll('#date_locale a.dropdown-item').forEach((el) => {
        el.addEventListener('click', function () {
            let locale = $(this).data('locale');
            asyncDateFormatLocale(promTable, locale);
        })
    });
});

async function asyncFilterColumn(promTable, i, value) {
    let table = await promTable;

    table.column(i).search(value, false, true).draw();
}

async function asyncFilterGlobal(promTable) {
    let table = await promTable;
    let filterGlobal = document.querySelector('#search-global');

    let startDate = $('#search-start-date').val();
    let endDate = $('#search-end-date').val();

    let createdAt = {};
    if (startDate !== "" || endDate !== "") {
        createdAt = {
            startDate: startDate,
            endDate: endDate
        }
    }
    let params = {
        search: filterGlobal.value,
        createdAt
    };
    table.search(JSON.stringify(params), false, true).draw();
}

const notifyUser = (message) => {
    $('#flash-notice').addClass("alert alert-primary").append("<p>" + message + "</p>");

    setTimeout(function () {
        $('#flash-notice').removeClass("alert alert-primary");
        $('#flash-notice p').remove();
    }, 3000);

}

function deleteAccount(table, el) {
    let accountId = el.dataset.accountId;

    let url = Routing.generate('api_account_delete', {id: accountId});

    getData(url).then((data) => {
        console.log("success");
        console.log(data);

        table.draw();
    }).catch(error => {
        console.log("error");
        console.log(error.message);
    });
}

async function getData(url = "") {

    const response = await fetch(url, {
        method: "GET",
        credentials: "include",
        redirect: "follow",
    });
    if (!response.ok) {
        const message = `An error has occured: ${response.status}`;
        throw new Error(message);
    }

    return await response.json();
}

async function asyncDateFormatLocale(promTable, locale) {
    let table = await promTable;

    let formatter = new Intl.DateTimeFormat(locale);

    table.rows().every(function (rowIdx, tableLoop, rowLoop) {
        let row = this.data();

        let date = new Date(Date.parse(row.createdAt));

        row.createdAt = formatter.format(date);
        // Update the data in Datatables data cache
        this.data(row);

    });
}