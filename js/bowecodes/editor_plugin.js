(function() {

        tinymce.create('tinymce.plugins.MCEbowecodes', {
                
                init : function(ed, url) {
                        
                        ed.addCommand('mcebowecodes', function() {

                            boweCodesLaunchEditor();
	
                        });
						
                        ed.addButton('MCEbowecodes', {
                                title : 'Build your Bowe code',
                                cmd : 'mcebowecodes',
                                image : url + '/img/bowe-codes-button.png'
                        });

                },

                
                createControl : function(n, cm) {
                        return null;
                },

                getInfo : function() {
                        return {
                                longname : 'Bowe Codes editor launcher',
                                author : 'imath',
                                authorurl : 'http://imathi.eu',
                                infourl : 'http://imathi.eu',
                                version : "1.0"
                        };
                }
        });

        // Register plugin
        tinymce.PluginManager.add('MCEbowecodes', tinymce.plugins.MCEbowecodes);
})();