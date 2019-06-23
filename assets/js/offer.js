let $ = require('jquery');

$(function () {

    let lang = $('html').prop('lang');

    let OfferApp = {
        handleDetailsLinkAdd: function () {
            let index = OfferApp.$detailsContainer.data('index');
            let element = OfferApp.$detailsContainer.data('prototype');
            element = element.replace(/__name__label__/g, index);
            element = element.replace(/__name__/g, index);
            let $element = $(element);
            $element.find('.js-detail-copy').on('click', OfferApp.handleDetailsLinkCopy);
            $element.find('.js-detail-delete').on('click', OfferApp.handleDetailsLinkDelete);
            OfferApp.$detailsContainer.data('index', index + 1);
            OfferApp.$detailsContainer.append($element);
            scrollToElement($element);
            $element.find(':input').eq(0).focus();
            OfferApp.handleDetailsUpdateAmounts($element.closest('div[id^="offer_details_"]'));
            let $parent = $element.closest('div[id^="offer_details_"]');
            OfferApp.handleDetailsUpdateAmounts($parent);
            $parent.find('input').on('change', function () {
                OfferApp.handleDetailsUpdateAmounts($parent);
            });
        },
        handleDetailsLinkCopy: function (e) {
            let $row = $(e.target).closest('div[id]');
            let index = OfferApp.$detailsContainer.data('index');
            let element = OfferApp.$detailsContainer.data('prototype');
            element = element.replace(/__name__label__/g, index);
            element = element.replace(/__name__/g, index);
            let $element = $(element);
            $row.find(':input').each(function (k) {
                $element.find(':input').eq(k).val($(this).val());
            });
            $element.find('.js-detail-copy').on('click', OfferApp.handleDetailsLinkCopy);
            $element.find('.js-detail-delete').on('click', OfferApp.handleDetailsLinkDelete);
            OfferApp.$detailsContainer.data('index', index + 1);
            OfferApp.$detailsContainer.append($element);
            scrollToElement($element);
            $element.find(':input').eq(0).focus();
            let $parent = $element.closest('div[id^="offer_details_"]');
            OfferApp.handleDetailsUpdateAmounts($parent);
            $parent.find('input').on('change', function () {
                OfferApp.handleDetailsUpdateAmounts($parent);
            });
        },
        handleDetailsLinkDelete: function (e) {
            $(e.target).closest('div[id]').slideUp('normal', function () {
                $(this).remove();
            });
        },
        handleDetailsUpdateAmounts: function ($element) {
            let amount = OfferApp.moneyUnFormat($element.find('[id$="_amountUnit"]').val());
            let quantity = OfferApp.moneyUnFormat($element.find('[id$="_quantity"]').val());
            $element.find('[id$="_amountTotal"]').val(OfferApp.moneyFormat(amount * quantity));
            this.handleUpdateAmounts();
        },
        handleUpdateAmounts: function () {
            let amount = 0.0;
            this.$detailsContainer.find('> div').each(function () {
                amount += OfferApp.moneyUnFormat($(this).find('[id$="_amountTotal"]').val());
            });
            $('#offer_amountExcludingTax').val(OfferApp.moneyFormat(amount));
            let taxRate = OfferApp.moneyUnFormat($('#offer_taxRate').val());
            let taxAmount = amount * taxRate / 100;
            $('#offer_taxAmount').val(OfferApp.moneyFormat(taxAmount));
            $('#offer_amountIncludingTax').val(OfferApp.moneyFormat(amount + taxAmount));
        },
        initialize: function ($detailsContainer, $detailsLinkAdd) {
            this.$detailsContainer = $detailsContainer;
            this.$detailsContainer.find('.js-detail-copy').on('click', this.handleDetailsLinkCopy);
            this.$detailsContainer.find('.js-detail-delete').on('click', this.handleDetailsLinkDelete);
            this.$detailsLinkAdd = $detailsLinkAdd;
            this.$detailsLinkAdd.on('click', this.handleDetailsLinkAdd);
            this.$detailsContainer.find('input').on('change', function () {
                OfferApp.handleDetailsUpdateAmounts($(this).closest('div[id^="offer_details_"]'));
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

    OfferApp.initialize($('#offer_details'), $('.js-detail-add'));

    let $client = $('#offer_client');

    $client.on('change', function () {
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
                    $('#offer_address_address').val(data.addressPrimary.address);
                    $('#offer_address_city').val(data.addressPrimary.city);
                    $('#offer_address_country').val(data.addressPrimary.country);
                    $('#offer_address_name').val(data.addressPrimary.name);
                    $('#offer_address_postcode').val(data.addressPrimary.postcode);
                }
            }
        });
    });

    let $addressName = $('#offer_address_name');

    if (1 === $addressName.length && 0 === $addressName.val().length) {
        $client.change();
    }

    let $issueDate = $('#offer_issueDate');
    let $validityDate = $('#offer_validityDate');
    $issueDate.on('change', function () {
        let value = new Date($(this).val());
        value.setMonth(value.getMonth() + 1);
        $validityDate.val(value.toISOString().substr(0, 10));
    });

});
