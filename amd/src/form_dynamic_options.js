define(['core/ajax'], function(Ajax) {
    return {
        /**
         * Process the results for auto complete elements.
         *
         * @param {String} selector The selector of the auto complete element.
         * @param {Array} results An array of results.
         * @return {Array} New array of results.
         */
        processResults: function(selector, results) {
            // Corrigido: Usando results ao invés de data
            return results.map(function(item) {
                return {
                    label: item.label,
                    value: item.value
                };
            });
        },

        /**
         * Source of data for Ajax element.
         *
         * @param {String} selector The selector of the auto complete element.
         * @param {String} query The query string.
         * @param {Function} callback A callback function receiving an array of results.
         * @param {Function} failure A callback function to be called in case of failure, receiving the error message.
         * @return {Void}
         */
        transport: function(selector, query, callback, failure) {
            // Obtem o elemento DOM correspondente ao selector
            var element = document.querySelector(selector);

            // Recupera o valor do atributo data-instance
            var instance = element ? element.getAttribute('data-instance') : null;
            // Chama o serviço mod_coursecertificate_form_template_options via AJAX
            Ajax.call([{
                methodname: 'customfield_dynamic_form_dynamic_options',
                args: {
                    search: query,
                    instance: instance // adiciona o atributo no args
                },
                done: function(response) {
                    // Chama o callback com os dados retornados
                    callback(response);
                },
                fail: function(error) {
                    // Chama a função de falha caso haja um erro
                    failure(error);
                }
            }]);
        }
    };
});
