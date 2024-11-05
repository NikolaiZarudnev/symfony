import '@stripe/stripe-js';

import {loadStripe} from '@stripe/stripe-js/pure';

const stripe = await loadStripe(process.env.STRIPE_KEY);

async function fetchAmount() {
    let res = await fetch(Routing.generate('api_cart_total'), {
        method: "POST",
    });

    return await res.json();
}

$(document).ready(async function () {
    let amount = await fetchAmount().then(function (res) {
        return res;
    });

    const options = {
        mode: 'payment',
        amount: amount,
        currency: 'usd',
        // Fully customizable with appearance API.
        appearance: {/*...*/},
    };
    let elements = stripe.elements(options);//get client secrete
    let paymentElement = elements.create('payment');

    paymentElement.mount('#payment-element');

    document.querySelector('#payment-form').addEventListener('submit', async function (event) {
        event.preventDefault();

        let submitBtn = $('#pay-btn');
        // Prevent multiple form submissions
        if (submitBtn.disabled) {
            return;
        }
        // Disable form submission while loading
        submitBtn.disabled = true;

        const {error: submitError} = await elements.submit();
        if (submitError) {
            handleError(submitError);
            return;
        }

        let url = Routing.generate('api_stripe_create_payment');
        postData(url, {}).then(async (data) => {
            let clientSecret = data.clientSecret;
            const {paymentIntent, error} = await stripe.confirmPayment({
                elements,
                clientSecret,
                redirect: "if_required",
                confirmParams: {
                    return_url: Routing.generate('app_order', {}, true),
                },
            });

            if (error) {
                //failed
                // This point is only reached if there's an immediate error when
                // confirming the payment. Show the error to your customer (for example, payment details incomplete)
                alert('failed');
                handleError(error);
            } else if (paymentIntent && paymentIntent.status === 'succeeded') {
                // The customer completed payment on your checkout page
                alert('succeeded');
                window.location = Routing.generate('app_order_show', {id: data.orderId}, true);

            } else if (paymentIntent && paymentIntent.status === 'requires_action') {
                // The customer didn’t complete the checkout
                alert('requires_action');
            } else if (paymentIntent && paymentIntent.status === 'canceled') {
                //
                alert('canceled');
            } else if (paymentIntent && paymentIntent.status === 'processing') {
                //
                alert('processing');
            } else if (paymentIntent && paymentIntent.status === 'requires_payment_method') {
                // The customer’s payment failed on your checkout page
                alert('requires_payment_method');
            }

        }).catch(error => {
            console.log(error);
            handleError(error);
        });
    });


})

async function postData(url = "", data = {}) {

    const response = await fetch(url, {
        method: "POST",
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