var TriggerForm = Class.create({
    initialize : function() {

        var defaultMessage = $("default-message-template");
        if (null === defaultMessage) {
            //this will be undefined on stage1 of the form
            return;
        }

        if (defaultMessage.innerHTML !== "") {
            var defaultButton =
                '<div class="reset-tmpl-btn-container"><button id="reset-template" type="button"' +
                'class="scalable">' + Translator.translate('Reset to Default Message') + '</button></div>';

            var availableVariables = $("available-variables-container");
            availableVariables.setStyle({
                height: '118px'
            });

            availableVariables.previous().setStyle({
                'padding-top': '14px'
            });

            var parent = availableVariables.up();
            parent.update(defaultButton + parent.innerHTML);

            var triggerMessage = $("trigger_message_template");
            Event.observe("reset-template", 'click', function(e) {
                triggerMessage.setValue(defaultMessage.innerHTML);
                this.updateMessageTotals($("trigger_message_template").value);
            }.bind(this));
        }

        this.updateMessageTotals($("trigger_message_template").value);
        Event.observe("trigger_message_template", 'keyup', function(e) {
            this.updateMessageTotals(e.target.value);
        }.bind(this));
    },

    updateMessageTotals: function(message) {
        var counter = $("message-counter");
        if (counter === null) {
            counter = '<div id="message-counter"></div>';
            var triggerMessage = $("trigger_message_template");
            triggerMessage.insert({after: counter});
            counter = $("message-counter");
        }

        //strip out variables
        message = message.replace(/\$[a-zA-Z]+[a-zA-Z\d_]*\$/ig, '');

        var smsStats = new SMSMessageStatistics(message);
        var totals = smsStats.getCharacterCount() + " "
            + Translator.translate('characters') + " / "
            + smsStats.getMessagePartCount() + " "
            + Translator.translate('SMS parts');

        //call Magento's form validator to validate the particular element
        //but only for the validate-message-count test
        Validation.test('validate-message-count', $('trigger_message_template'));

        totals = '<p class="not-no-bg"><small>' + totals + '</small></div>';
        counter.update(totals);
    },

    removeSuccessMessages: function() {
        //delete old saved success message, in-case validation failed
        $$('ul.messages > li.success-msg').each(function(element) {
            element.remove();
        });
    },

    submit: function() {
        this.removeSuccessMessages();
        editForm.submit();
    },

    submitAndContinue: function() {
        this.removeSuccessMessages();
        saveAndContinueEdit();
    }
});

Validation.add('validate-sender-format', 'The Sender ID must be between 1 and 11 characters when it contains letters, numbers, spaces and special characters (* $ ? ! ” # % & _ - , . @ \' +), or between 5 and 20 when it contains just numbers.', function(v) {
    if (v.match(/^\d+$/)) {
        return v.match(/^\d{5,20}$/);
    }
    return v.match(/^[A-Za-z\d\*\$\?!"#%&_\-,\.@'\+\s]{1,11}$/);
});

Validation.add('validate-message-count', 'Message must be between 1 and 612 characters', function(v) {
    //strip out variables
    v = v.replace(/\$[a-zA-Z]+[a-zA-Z\d_]*\$/ig, '');
    var smsStats = new SMSMessageStatistics(v);
    return smsStats.getCharacterCount() <= smsStats.maximumCharacterCount;
});

/**
 * Validate whether there are variables used which are not available
 */
Validation.add('validate-variables', 'Message contains variables which are not available', function(v) {
    var availableVariablesLi    = $("available-variables").childElements();
    var availableVariables      = [];

    for (var i = 0; i < availableVariablesLi.length; i++) {
        var textContent = availableVariablesLi[i].textContent || availableVariablesLi[i].innerText;
        availableVariables.push(textContent);
    }

    var variables = v.match(/\$[a-zA-Z]+[a-zA-Z\d_]*\$/ig);

    if (null === variables) {
        //no variables were used
        return true;
    }

    for (var i = 0; i < variables.length; i++) {
        variables[i] = variables[i].toUpperCase();
    }

    var notAvailableVariables = variables.filter(function(variable) {
        return availableVariables.indexOf(variable) === -1;
    });

    return notAvailableVariables.length === 0;
});

var SMSMessageStatistics = function (messageText, onMessageTruncated) {
    this.messageText = messageText;
    this.messageTruncatedCallback = onMessageTruncated;
    this.maximumCharacterCount = 612;
};

SMSMessageStatistics.prototype = {
    getCharacterCount: function () {
        var getExtendedCharacterCount = function (messageText) {
            var extendedCharacterList = '^{}\\[]~|\u20AC';
            var extendedCharacters = 0;

            for (var i = 0; i < messageText.length; i++) {
                if (extendedCharacterList.indexOf(messageText.charAt(i)) > -1) {
                    extendedCharacters++;
                }
            }
            return extendedCharacters;
        };

        var trimEnd = function(messageText) {
            return messageText.replace(/\s+$/g, '');
        };

        var inputText = trimEnd(this.messageText.replace(/\r\n/g, '\n'));
        var extendedCharacterCount = getExtendedCharacterCount(inputText);
        var characterCount = inputText.length + extendedCharacterCount;

        if (this.messageTruncatedCallback) {
            var characterLimit = this.maximumCharacterCount - extendedCharacterCount;
            if (characterCount > this.maximumCharacterCount) {
                inputText = trimEnd(this.messageText.substring(0, characterLimit));

                this.messageTruncatedCallback(inputText);

                extendedCharacterCount = getExtendedCharacterCount(inputText);
                characterCount = inputText.length + extendedCharacterCount;
            }
        }

        return characterCount;
    },
    getMessagePartCount: function () {
        var characterCount = this.getCharacterCount();
        var messagePartsCount = 1;

        if (characterCount >= 161 && characterCount <= 306) {
            messagePartsCount = 2;
        } else if (characterCount >= 307 && characterCount <= 459) {
            messagePartsCount = 3;
        } else if (characterCount >= 460) {
            messagePartsCount = 4;
        }

        return messagePartsCount;
    },
    getCreditCount: function (recipientsCount) {
        return this.getMessagePartCount() * recipientsCount;
    }
};

