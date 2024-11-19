define([
    'Otto/Plugin/Messages',
], function (MessageObj) {

    window.WizardInstallationOtto = Class.create(Common, {

        continueStep: function () {
            if (WizardObj.steps.current.length) {
                this[WizardObj.steps.current + 'Step']();
            }
        },

        // Steps
        // ---------------------------------------

        registrationStep: function () {
            WizardObj.registrationStep(Otto.url.get('wizard_registration/createLicense'));
        },

        settingsStep: function () {
            this.initFormValidation();

            if (!this.isValidForm()) {
                return false;
            }

            this.submitForm(Otto.url.get('wizard_installationOtto/settingsContinue'));
        },

        listingTutorialStep: function () {
            WizardObj.setStep(WizardObj.getNextStep(), function () {
                WizardObj.complete();
            });
        }

        // ---------------------------------------
    });
});
