let $ = require('jquery');

$(function () {

    let InvoicingAddressApp = {
        handleDisplay: function () {
            if (InvoicingAddressApp.$switch.prop('checked')) {
                InvoicingAddressApp.$collapse.hide();
            } else {
                InvoicingAddressApp.$collapse.show();
            }
        },
        initialize: function ($switch, $collapse) {
            this.$collapse = $collapse;
            this.$switch = $switch;
            this.$switch.on('click', this.handleDisplay);
            this.handleDisplay();
        }
    };

    InvoicingAddressApp.initialize($('#client_addressInvoicingSamePrimary'), $('#addressInvoicing'));

});
