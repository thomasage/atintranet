let $ = require('jquery');

$(function () {

    let AppAdminUserEdit = {
        handleChangeRole: function () {
            console.log(AppAdminUserEdit.$rolesSelect.val());
            $.ajax({
                url: AppAdminUserEdit.$rolesSelect.data('client-location-url'),
                data: {
                    roles: AppAdminUserEdit.$rolesSelect.val()
                },
                success: function (html) {
                    if (!html) {
                        AppAdminUserEdit.$clientDestination.find('select').remove();
                        AppAdminUserEdit.$clientDestination.addClass('d-none');
                        return;
                    }
                    AppAdminUserEdit.$clientDestination.html(html).removeClass('d-none');
                }
            });
        },
        initialize: function ($rolesSelect, $clientDestination) {
            this.$clientDestination = $clientDestination;
            this.$rolesSelect = $rolesSelect;
            this.$rolesSelect.on('change', this.handleChangeRole);
        }
    };

    AppAdminUserEdit.initialize($('.js-user-form-roles'), $('.js-client-location-target'),);

});
