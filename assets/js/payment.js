let $ = require('jquery');

$(function () {

    let PaymentApp = {
        handleDetailsLinkAdd: function (e) {
            e.preventDefault();
            let index = PaymentApp.$detailsContainer.data('index');
            let element = PaymentApp.$detailsContainer.data('prototype');
            element = element.replace(/__name__label__/g, index);
            element = element.replace(/__name__/g, index);
            let $element = $(element);
            $element.find('a.js-invoice-delete').on('click', PaymentApp.handleDetailsLinkDelete);
            PaymentApp.$detailsContainer.data('index', index + 1);
            PaymentApp.$detailsContainer.append($element);
            scrollToElement($element);
            $element.find(':input').eq(0).focus();
        },
        handleDetailsLinkDelete: function (e) {
            e.preventDefault();
            $(e.target).closest('[id]').slideUp('normal', function () {
                $(this).remove();
                scrollToElement(PaymentApp.$detailsContainer);
            });
        },
        initialize: function ($detailsContainer, $detailsLinkAdd) {
            this.$detailsContainer = $detailsContainer;
            this.$detailsContainer.data('index', this.$detailsContainer.find('> div').length);
            this.$detailsContainer.find('a.js-invoice-delete').on('click', this.handleDetailsLinkDelete);
            this.$detailsLinkAdd = $detailsLinkAdd;
            this.$detailsLinkAdd.on('click', this.handleDetailsLinkAdd);
        }
    };

    PaymentApp.initialize($('#payment_paymentInvoices'), $('a.js-invoice-add'));

    let cache = {};
    $('#payment_thirdPartyName').autocomplete({
        minLength: 1,
        source: function (request, response) {
            let term = request.term;
            if (term in cache) {
                response(cache[term]);
                return;
            }
            $.getJSON('/payment/autocomplete/thirdPartyName', request, function (data, status, xhr) {
                cache[term] = data;
                response(data);
            });
        }
    });

});
