<script src="https://js.stripe.com/v3/"></script>

<script type="text/javascript">
    var stripe = Stripe('{{ config('fi.merchant_Stripe_publishableKey') }}');

    stripe.redirectToCheckout({
        sessionId: '{{ $stripeSessionId }}'
    }).then(function (result) {

    });
</script>