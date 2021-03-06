let $ = require('jquery');

$(function () {

    let lang = $('html').prop('lang');

    let InvoiceApp = {
        handleDetailsLinkAdd: function () {
            let index = InvoiceApp.$detailsContainer.data('index');
            let element = InvoiceApp.$detailsContainer.data('prototype');
            element = element.replace(/__name__label__/g, index);
            element = element.replace(/__name__/g, index);
            let $element = $(element);
            $element.find('.js-detail-copy').on('click', InvoiceApp.handleDetailsLinkCopy);
            $element.find('.js-detail-delete').on('click', InvoiceApp.handleDetailsLinkDelete);
            InvoiceApp.$detailsContainer.data('index', index + 1);
            InvoiceApp.$detailsContainer.append($element);
            scrollToElement($element);
            $element.find(':input').eq(0).focus();
            InvoiceApp.handleDetailsUpdateAmounts($element.closest('div[id^="invoice_details_"]'));
            let $parent = $element.closest('div[id^="invoice_details_"]');
            InvoiceApp.handleDetailsUpdateAmounts($parent);
            $parent.find('input').on('change', function () {
                InvoiceApp.handleDetailsUpdateAmounts($parent);
            });
        },
        handleDetailsLinkCopy: function (e) {
            let $row = $(e.target).closest('div[id]');
            let index = InvoiceApp.$detailsContainer.data('index');
            let element = InvoiceApp.$detailsContainer.data('prototype');
            element = element.replace(/__name__label__/g, index);
            element = element.replace(/__name__/g, index);
            let $element = $(element);
            $row.find(':input').each(function (k) {
                $element.find(':input').eq(k).val($(this).val());
            });
            $element.find('.js-detail-copy').on('click', InvoiceApp.handleDetailsLinkCopy);
            $element.find('.js-detail-delete').on('click', InvoiceApp.handleDetailsLinkDelete);
            InvoiceApp.$detailsContainer.data('index', index + 1);
            InvoiceApp.$detailsContainer.append($element);
            scrollToElement($element);
            $element.find(':input').eq(0).focus();
            let $parent = $element.closest('div[id^="invoice_details_"]');
            InvoiceApp.handleDetailsUpdateAmounts($parent);
            $parent.find('input').on('change', function () {
                InvoiceApp.handleDetailsUpdateAmounts($parent);
            });
        },
        handleDetailsLinkDelete: function (e) {
            $(e.target).closest('div[id]').slideUp('normal', function () {
                $(this).remove();
            });
        },
        handleDetailsUpdateAmounts: function ($element) {
            let amount = InvoiceApp.moneyUnFormat($element.find('[id$="_amountUnit"]').val());
            let quantity = InvoiceApp.moneyUnFormat($element.find('[id$="_quantity"]').val());
            $element.find('[id$="_amountTotal"]').val(InvoiceApp.moneyFormat(amount * quantity));
            this.handleUpdateAmounts();
        },
        handleUpdateAmounts: function () {
            let amount = 0.0;
            this.$detailsContainer.find('> div').each(function () {
                amount += InvoiceApp.moneyUnFormat($(this).find('[id$="_amountTotal"]').val());
            });
            $('#invoice_amountExcludingTax').val(InvoiceApp.moneyFormat(amount));
            let taxRate = InvoiceApp.moneyUnFormat($('#invoice_taxRate').val());
            let taxAmount = amount * taxRate / 100;
            $('#invoice_taxAmount').val(InvoiceApp.moneyFormat(taxAmount));
            $('#invoice_amountIncludingTax').val(InvoiceApp.moneyFormat(amount + taxAmount));
        },
        initialize: function ($detailsContainer, $detailsLinkAdd) {
            this.$detailsContainer = $detailsContainer;
            this.$detailsContainer.find('.js-detail-copy').on('click', this.handleDetailsLinkCopy);
            this.$detailsContainer.find('.js-detail-delete').on('click', this.handleDetailsLinkDelete);
            this.$detailsLinkAdd = $detailsLinkAdd;
            this.$detailsLinkAdd.on('click', this.handleDetailsLinkAdd);
            this.$detailsContainer.find('input').on('change', function () {
                InvoiceApp.handleDetailsUpdateAmounts($(this).closest('div[id^="invoice_details_"]'));
            });
        },
        moneyFormat: function (string) {
            return string.toFixed(2).toLocaleString(lang, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        },
        moneyUnFormat: function (string) {
            var parts = (1234.5).toLocaleString(lang).match(/(\D+)/g);
            var unformatted = string;
            unformatted = unformatted.split(parts[0]).join("");
            unformatted = unformatted.split(parts[1]).join(".");
            if ('' === unformatted) {
                unformatted = 0;
            }
            return parseFloat(unformatted);
        }
    };

    InvoiceApp.initialize($('#invoice_details'), $('.js-detail-add'));

    let client = $('#invoice_client');

    client.on('change', function () {
        let client = $(this).val();
        let url = $(this).data('url');
        $.ajax({
            url: url,
            method: 'GET',
            data: {
                client: client,
            },
            success: function (data) {
                if (data.addressPrimary) {
                    $('#invoice_address_address').val(data.addressPrimary.address);
                    $('#invoice_address_city').val(data.addressPrimary.city);
                    $('#invoice_address_country').val(data.addressPrimary.country);
                    $('#invoice_address_name').val(data.addressPrimary.name);
                    $('#invoice_address_postcode').val(data.addressPrimary.postcode);
                }
            }
        });
    });

    let addressName = $('#invoice_address_name');

    if (1 === addressName.length && 0 === addressName.val().length) {
        client.change();
    }

    let issueDate = $('#invoice_issueDate');
    let dueDate = $('#invoice_dueDate');
    issueDate.on('change', function () {
        let value = new Date($(this).val());
        value.setMonth(value.getMonth() + 1);
        dueDate.val(value.toISOString().substr(0, 10));
    });

    $('#js-action-duplicate').on('click', (e) => {
        let $element = $(e.currentTarget);
        if (!window.confirm($element.data('confirm'))) {
            return;
        }
        window.location.href = $element.data('url');
    });

});
