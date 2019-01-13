let $ = require('jquery');

$(function () {

    let InvoiceApp = {
        handleDetailsLinkAdd: function (e) {
            e.preventDefault();
            let index = InvoiceApp.$detailsContainer.data('index');
            let element = InvoiceApp.$detailsContainer.data('prototype');
            element = element.replace(/__name__label__/g, index);
            element = element.replace(/__name__/g, index);
            let $element = $(element);
            console.log($element);
    //         $element.find('a.js-detail-delete').on('click', InvoiceApp.handleDetailsLinkDelete);
    //         InvoiceApp.$detailsContainer.data('index', index + 1);
    //         InvoiceApp.$detailsContainer.append($element);
    //         scrollToElement($element);
        },
    //     handleDetailsLinkDelete: function (e) {
    //         e.preventDefault();
    //         $(e.target).closest('tr').remove();
    //         scrollToElement(InvoiceApp.$detailsContainer.parent());
    //     },
        initialize: function ($detailsContainer, $detailsLinkAdd) {
            this.$detailsContainer = $detailsContainer;
    //         this.$detailsContainer.data('index', this.$detailsContainer.find('tr').length);
    //         this.$detailsContainer.find('a.js-detail-delete').on('click', this.handleDetailsLinkDelete);
            this.$detailsLinkAdd = $detailsLinkAdd;
            this.$detailsLinkAdd.on('click', this.handleDetailsLinkAdd);
    //         this.updateTotals();
        },
    //     updateTotals: function () {
    //         let amountExcludingTax = 0;
    //         let amountTax = 0;
    //         let amountIncludingTax = 0;
    //         let amountUnit, quantity, taxRate;
    //         this.$detailsContainer.find('tr').each(function () {
    //             amountUnit = parseFloat($(this).find('[id$="_amountUnit"]').val());
    //             quantity = parseFloat($(this).find('[id$="_quantity"]').val());
    //             taxRate = parseFloat($(this).find('[id$="_taxRate"]').val());
    //             amountExcludingTax += amountUnit * quantity;
    //             amountTax += amountUnit * quantity * taxRate / 100;
    //             amountIncludingTax += amountUnit * quantity * (1 + taxRate / 100);
    //         });
    //         this.$detailsContainer.closest('table').find('#invoice_amountExcludingTax').val(amountExcludingTax.toFixed(2));
    //         this.$detailsContainer.closest('table').find('#invoice_taxAmount').val(amountTax.toFixed(2));
    //         this.$detailsContainer.closest('table').find('#invoice_amountIncludingTax').val(amountIncludingTax.toFixed(2));
    //     }
    };

    InvoiceApp.initialize($('#invoice_details'), $('a.js-detail-add'));

});
