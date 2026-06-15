import { register } from '@shopify/web-pixels-extension';

register(({ analytics, browser, settings, init }) => {

    const backendUrl = settings.api_url;

    if (! backendUrl) {
        console.log('Backend url not found');
        return;
    }

    const url = new URL(init.context.document.location.href);
    const clickId = url.searchParams.get('clickid');

    if (clickId) {
        browser.localStorage.setItem('monevibe_click_id', clickId).then(() => {
            console.log('Monevibe click_id saved!', clickId);
        });
    }

    const sendToBackend = async (eventName, payload) => {
        const storedId = await browser.localStorage.getItem('monevibe_click_id');

        if (!storedId) {
            console.warn(`Monevibe click_id empty`);
            return;
        }

        console.log(backendUrl);

        try {
            await fetch(backendUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    event: eventName,
                    click_id: storedId,
                    timestamp: new Date().toISOString(),
                    ...payload
                }),
                keepalive: true
            });
        } catch (e) {
            console.error('Error', e);
        }
    };

    analytics.subscribe('checkout_completed', async (event) => {
        const checkout = event.data.checkout;

        console.log(event);

        await sendToBackend('checkout_completed', {
            external_id: checkout.order.id,
            total_amount: checkout.totalPrice.amount,
            currency: checkout.totalPrice.currencyCode,
            shop: event.context.document.location.hostname,
        });
    });
});