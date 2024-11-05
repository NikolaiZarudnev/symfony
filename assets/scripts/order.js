const PAID = 1;
const CANCELLED = 2;
const PROCESSING = 3;
$(document).ready(function () {
    let orderStatus = $('#order-status');
    if (orderStatus.get().length > 0) {
        updateOrderStatus(orderStatus);
    }

    let headerCartCountProducts = $('#header-cart-count-products');
    if (headerCartCountProducts.get().length > 0) {
        updateCartCountProducts(headerCartCountProducts);
    }

    $('.add-product-to-order').on('click', addProductToOrder);

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

    $(document).on('click', '.order-refund', function () {
        $('#order-refund-modal-confirm').data('order-id', $(this).data('order-id'));
    });

    $(document).on('click', '#order-refund-modal-confirm', function () {

        let url = Routing.generate('api_stripe_refund');

        refundOrder(url, {orderId: $(this).data('order-id')}).then((data) => {
            console.log('postData refunded');
            console.log(data);
        }).catch(error => {
            console.log(error);
            handleError(error);
        });
    });

    $('.remove-product').on('click', removeProduct);
});
function addProductToOrder() {
    let productId = $(this).data('product-id');

    $.ajax({
        url: Routing.generate('api_cart_add_product'),
        type: "POST",
        dataType: "json",
        data: {
            "productId": productId
        },
        success: function (data) {
            console.log(data);
            updateCartCountProducts($('#header-cart-count-products'));
        }
    });
}
async function updateOrderStatus(orderStatus) {
    let orderId = orderStatus.data('order-id');

    let status = await fetchStatus(orderId);

    let item = document.createElement('span');

    switch (status) {
        case PAID:
            item.style.color = 'green';
            item.textContent = orderStatus.data('status-paid-text');
            break;
        case CANCELLED:
            item.style.color = 'red';
            item.textContent = orderStatus.data('status-cancelled-text');
            break;
        case PROCESSING:
            item.style.color = '#ff960c';
            item.textContent = orderStatus.data('status-processing-text');
            break;
    }
    orderStatus.html('Order status: ');
    orderStatus.append(item);
}
async function updateCartCountProducts(headerCartCountProducts) {
    let countProducts = await fetchData(Routing.generate('api_cart_count_products'));

    headerCartCountProducts.html('(' + countProducts + ')');
}
function removeProduct() {
    let productId = $(this).data('product-id');

    $.ajax({
        url: Routing.generate('api_cart_remove_product'),
        type: "POST",
        dataType: "json",
        data: {
            "productId": productId
        },
        success: function (data) {
            console.log(data);
            $('#product-' + data).remove();
            fetchData(Routing.generate('api_cart_total')).then((totalCost) => {
                let orderTotalCost = $('#order-total-cost');
                orderTotalCost.html(orderTotalCost.data('order-total-cost-text') + ': $' + totalCost / 100);
            });
            updateCartCountProducts($('#header-cart-count-products'));
        }
    });
}

async function fetchData(url) {
    let res = await fetch(url, {
        method: "POST",
    });

    return await res.json();
}


async function fetchStatus(orderId) {
    const data = new FormData();
    data.append('orderId', orderId);

    let res = await fetch(Routing.generate('api_order_status'), {
        method: "POST",
        body: data,
    });

    return await res.json();
}

async function refundOrder(url = "", data = {}) {
    const formData = new FormData();
    formData.append('orderId', data.orderId);

    const response = await fetch(url, {
        method: "POST",
        body: formData,
    });
    if (!response.ok) {
        const message = `An error has occured: ${response.status}`;
        throw new Error(message);
    }

    return await response.json();
}

const handleError = (error) => {
    const messageContainer = document.querySelector('#error-message');
    messageContainer.textContent = error.message;
}