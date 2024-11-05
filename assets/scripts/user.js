$(document).ready(function () {
    document.querySelectorAll('.isActive')
        .forEach(btn => {
            btn.addEventListener("click", addButtonToCollection)
        });

    $('#user_create').submit(createUser);

    let timeout = null;
    let prevValue = null;
    $('#user-search-input').on('keyup', function () {
        let curValue = $(this).val().trim();
        if (curValue === prevValue) {
            return;
        }
        prevValue = curValue;
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            searchUser();

        }, 300);

    });

    $('#user-search-input').on('click', function () {
        if ($(this).val().length > 2) {
            return;
        }
        console.log(getItemsSearchHistory());

        let searchResult = $("#search-result");

        if (getItemsSearchHistory().length > 0) {
            listHistory(searchResult);
            $(searchResult).show();
        } else {
            $(searchResult).hide();
        }

    });

    document.addEventListener('click', (e)=> {
        if (e.target.closest("#search-result")) return;

        $("#search-result").hide();

    })
})

const addButtonToCollection = (e) => {
    let userId = e.currentTarget.dataset.userId;
    let btn = e.currentTarget;
    $.ajax({
        url: Routing.generate('api_user_change_active', {id: userId}),
        type: "POST",
        dataType: "json",
        data: {
            "id": userId
        },
        success: function (data) {
            if (data['isActive']) {
                $(btn)
                    .removeClass('btn-outline-danger')
                    .addClass('btn-outline-success')
                    .html('Yes');
            } else {
                $(btn)
                    .removeClass('btn-outline-success')
                    .addClass('btn-outline-danger')
                    .html('No');
            }
        }
    });
}

const createUser = function () {
    let userId = $(this).data('user-id');
    let userLocale = $(this).data('locale');

    let formData = {
        email: $("#user_create input#email").val(),
        password: $("#user_create input#password").val(),
        roles: {0: $("#user_create select#role option:selected").val()},
        isActive: !!$("#user_create input#isActive").is(':checked'),
    };

    formData = JSON.stringify(formData);

    let params = {
        userJson: formData,
        userLocale: userLocale,
    };
    let url;
    if (userId) {
        url = Routing.generate('api_user_edit', {id: userId});
    } else {
        url = Routing.generate('api_user_create', {});
    }

    $.ajax({
        url: url,
        type: "POST",
        dataType: "json",
        data: params,
        success: function (data) {
            window.history.pushState(null, '', data.url);
            console.log(data);
            $('#user_create').data('user-id', data.id).data('locale', data.locale);

            notifyUser(data.message);
        },
        error: function (xhr) {
            console.log(xhr);
            if (xhr.status > 499) {
                let errors = xhr.responseJSON;
                console.log(errors);
                $.each(errors, function (key, value) {
                    $(".error#" + value.propertyPath).html(value.message)
                });
            }
        }
    });

    return false;
}

async function postData(url = "", data = {}) {
    let formData = new FormData();
    formData.append('userJson', data.userJson);
    formData.append('searchJson', data.searchJson);
    formData.append('userLocale', data.userLocale);

    const response = await fetch(url, {
        method: "POST",
        credentials: "include",
        redirect: "follow",
        body: formData,
    });
    if (!response.ok) {
        const message = `An error has occured: ${response.status}`;
        throw new Error(message);
    }

    return await response.json();
}

const searchUser = function () {
    let inputValue = $('#user-search-input').val();
    if (inputValue.length < 3) {
        return;
    }

    let userLocale = $('#user-search').data('locale');

    let formData = {
        searchString: inputValue
    };

    formData = JSON.stringify(formData);

    let params = {
        searchJson: formData,
        userLocale: userLocale,
    };

    let url = Routing.generate('api_user_search');

    postData(url, params).then((data) => {
        console.log("success");
        console.log(data);

        let list = $("#search-result");

        if (data.users.length > 0) {
            listUsers(list, data);
        } else {
            list.empty();

            let newItemList = $('<a>Not found</a>');
            newItemList.addClass("btn dropdown-item");
            list.append($('<li></li>')).append(newItemList);

        }
        list.show();
    }).catch(error => {
        console.log("error");
        console.log(error.message);
    });
}

function listHistory(list) {
    list.empty();
    list.append($("<span class=''>History:</span><div class='dropdown-divider'></div>"));
    getItemsSearchHistory().forEach((itemHistoryJson) => {

        let newItemList = $('<a></a>');
        let itemHistory = JSON.parse(itemHistoryJson);
        newItemList.html(itemHistory.email);
        newItemList.attr('href', itemHistory.url);
        newItemList.addClass("btn dropdown-item secondary-link");

        list.append($('<li></li>')).append(newItemList);
    });
}

const listUsers = (element, data) => {
    element.empty();

    data.users.forEach((user) => {
        let item = $("<a></a>");
        let url = Routing.generate('app_user_edit', {_locale: data.locale, id: user.id});
        // url = url.replace(
        //     "undefined",
        //     user.id
        // )

        item.attr('href', url).attr('id', "user-" + user.id);
        item.addClass("btn dropdown-item");

        item.data('user-id', user.id);
        item.html(user.email);
        element.append(item);

        item.on('click', function () {
            setSearchHistory(JSON.stringify({
                email: user.email,
                url: url
            }));
        });
    });
}

const notifyUser = (message) => {
    $('#flash-notice').addClass("alert alert-primary").append("<p>" + message + "</p>");

    setTimeout(function () {
        $('#flash-notice').removeClass("alert alert-primary");
        $('#flash-notice p').remove();
    }, 3000);

}

function getCookie(name) {
    let matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}

function deleteCookie(name) {
    setCookie(name, "", {
        'max-age': -1
    })
}

function setCookie(name, value, options = {}) {

    options = {
        path: '/',
        // при необходимости добавьте другие значения по умолчанию
        ...options
    };

    if (options.expires instanceof Date) {
        options.expires = options.expires.toUTCString();
    }

    let updatedCookie = encodeURIComponent(name) + "=" + encodeURIComponent(value);

    for (let optionKey in options) {
        updatedCookie += "; " + optionKey;
        let optionValue = options[optionKey];
        if (optionValue !== true) {
            updatedCookie += "=" + optionValue;
        }
    }

    document.cookie = updatedCookie;
}

const localStorageSplit = ';';

function setSearchHistory(value) {
    let items = getItemsSearchHistory();
    if (!items.includes(value)) {
        items.push(value);
    }
    localStorage.setItem('searchHistory', items.join(localStorageSplit));
}

function getItemsSearchHistory() {
    let lStorage = localStorage.getItem('searchHistory');

    return lStorage ? lStorage.split(localStorageSplit) : [];
}