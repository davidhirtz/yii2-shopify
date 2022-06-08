class Shopify {
    storageKey = 'shopifyCheckout';
    itemCount = 0;
    checkout = null;

    constructor(config) {
        const _ = this;

        _.client = ShopifyBuy.buildClient(config);

        if (localStorage.getItem(_.storageKey)) {
            _.client.checkout.fetch(localStorage.getItem(_.storageKey)).then(_.setCheckout);
        } else {
            _.createCheckout();
        }
    }

    setCheckout = (checkout) => {
        const _ = this;

        if (checkout) {
            _.checkout = checkout;
            _.updateCheckout();
        } else {
            _.createCheckout();
        }
    }

    createCheckout = () => {
        const _ = this;

        _.client.checkout.create().then(function (checkout) {
            localStorage.setItem(_.storageKey, checkout.id);
            _.checkout = checkout;
        });
    }

    updateCheckout = () => {
        const _ = this;

        if (_.checkout) {
            const itemCount = _.checkout.lineItems.length;

            if (_.itemCount != itemCount) {
                _.itemCount = itemCount;
                _.onItemCountChange();
            }
            // } else {
            //     setTimeout(_.updateCheckoutCount, 100);
            _.afterCheckoutUpdate();
        }
    }

    addLineItem = (variantId, quantity = 1) => {
        const _ = this;

        return _.client.checkout.addLineItems(_.checkout.id, [
            {
                variantId: btoa('gid://shopify/ProductVariant/' + variantId),
                quantity: quantity
            }
        ]).then(_.setCheckout);
    }

    updateLineItem = (lineItemId, quantity) => {
        const _ = this;

        return _.client.checkout.updateLineItems(_.checkout.id, [
            {
                id: lineItemId,
                quantity: quantity
            }
        ]).then(_.setCheckout);
    }

    removeLineItem = (lineItemId) => {
        const _ = this;
        return _.client.checkout.removeLineItems(_.checkout.id, [lineItemId]).then(_.setCheckout);
    }

    formatPrice = (price) => {
        return parseFloat(price).toLocaleString(this.client.config.language || undefined, {minimumFractionDigits: 2})
    }

    onItemCountChange = () => {
    }

    afterCheckoutUpdate = () => {
    }
}