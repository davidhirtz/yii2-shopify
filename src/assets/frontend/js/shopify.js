class Shopify {
    constructor(config) {
        const _ = this;

        _.storageKey = 'shopifyCheckout';
        _.itemCount = 0;
        _.checkout = null;

        _.client = ShopifyBuy.buildClient(config);

        _.setCheckout = (checkout) => {
            if (checkout) {
                _.checkout = checkout;
                _.updateCheckout();
            } else {
                _.createCheckout();
            }
        }

        _.createCheckout = () => {
            _.client.checkout.create().then(function (checkout) {
                localStorage.setItem(_.storageKey, checkout.id);
                _.checkout = checkout;
            });
        }

        _.updateCheckout = () => {
            if (_.checkout) {
                const itemCount = _.checkout.lineItems.length;

                if (_.itemCount != itemCount) {
                    _.itemCount = itemCount;
                    _.onItemCountChange();
                }

                _.afterCheckoutUpdate();
            }
        }

        _.addLineItem = (variantId, quantity = 1) => {
            return _.client.checkout.addLineItems(_.checkout.id, [
                {
                    variantId: btoa('gid://shopify/ProductVariant/' + variantId),
                    quantity: quantity
                }
            ]).then(_.setCheckout);
        }

        _.updateLineItem = (lineItemId, quantity) => {
            return _.client.checkout.updateLineItems(_.checkout.id, [
                {
                    id: lineItemId,
                    quantity: quantity
                }
            ]).then(_.setCheckout);
        }

        _.removeLineItem = (lineItemId) => {
            return _.client.checkout.removeLineItems(_.checkout.id, [lineItemId]).then(_.setCheckout);
        }

        _.formatPrice = (price) => {
            return parseFloat(price).toLocaleString(this.client.config.language || undefined, {minimumFractionDigits: 2})
        }

        _.onItemCountChange = () => {
        }

        _.afterCheckoutUpdate = () => {
        }

        if (localStorage.getItem(_.storageKey)) {
            _.client.checkout.fetch(localStorage.getItem(_.storageKey)).then(_.setCheckout);
        } else {
            _.createCheckout();
        }
    }
}