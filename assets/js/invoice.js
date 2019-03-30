let $ = require('jquery');

$(function () {

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
        },
        handleDetailsLinkDelete: function (e) {
            $(e.target).closest('div[id]').slideUp('normal', function () {
                $(this).remove();
            });
        },
        initialize: function ($detailsContainer, $detailsLinkAdd) {
            this.$detailsContainer = $detailsContainer;
            this.$detailsContainer.find('.js-detail-copy').on('click', this.handleDetailsLinkCopy);
            this.$detailsContainer.find('.js-detail-delete').on('click', this.handleDetailsLinkDelete);
            this.$detailsLinkAdd = $detailsLinkAdd;
            this.$detailsLinkAdd.on('click', this.handleDetailsLinkAdd);
        }
    };

    InvoiceApp.initialize($('#invoice_details'), $('.js-detail-add'));

    $('#invoice_client').on('change', function () {
        let client = $(this).val();
        let url = $(this).data('url');
        $.ajax({
            url: url,
            method: 'GET',
            data: {
                client: client,
            },
            success: function (data) {
                $('#invoice_address_address').val(data.addressPrimary.address);
                $('#invoice_address_city').val(data.addressPrimary.city);
                $('#invoice_address_country').val(data.addressPrimary.country);
                $('#invoice_address_name').val(data.addressPrimary.name);
                $('#invoice_address_postcode').val(data.addressPrimary.postcode);
            }
        });
    }).change();

});
