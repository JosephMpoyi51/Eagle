(function($){
    const builder = {
        init() {
            this.cache();
            this.bindEvents();
            this.ensureFieldNames();
        },
        cache() {
            this.$fields = $('#eagle-forms-fields');
            this.$addButton = $('#eagle-forms-add-field');
            this.fieldTypes = $('#eagle-forms-builder').data('field-types') || {};
        },
        bindEvents() {
            const self = this;
            this.$fields.sortable({
                handle: '.dashicons-move',
                update() {
                    self.refreshIndexes();
                }
            });

            this.$addButton.on('click', () => {
                self.addField();
            });

            this.$fields.on('click', '.eagle-forms-remove-field', function(){
                $(this).closest('.eagle-forms-field').remove();
                self.refreshIndexes();
            });
        },
        ensureFieldNames() {
            this.$fields.find('li').each(function(index){
                const $name = $(this).find('input[name*="[name]"]');
                if (!$name.val()) {
                    $name.val('field_' + (index + 1));
                }
            });
        },
        addField() {
            const index = this.$fields.children().length;
            const fieldHtml = this.getFieldTemplate(index);
            this.$fields.append(fieldHtml);
        },
        refreshIndexes() {
            this.$fields.children().each((index, el) => {
                const $el = $(el);
                $el.attr('data-index', index);
                $el.find('input, select, textarea').each(function(){
                    const name = $(this).attr('name');
                    if (!name) {
                        return;
                    }
                    const updated = name.replace(/\[\d+\]/, '[' + index + ']');
                    $(this).attr('name', updated);
                });
            });
        },
        getFieldTemplate(index) {
            let options = '';
            $.each(this.fieldTypes, function(type, label){
                options += '<option value="' + type + '">' + label + '</option>';
            });

            return [
                '<li class="eagle-forms-field" data-index="' + index + '">',
                '   <span class="dashicons dashicons-move"></span>',
                '   <div class="eagle-forms-field-inner">',
                '       <p>',
                '           <label>Label<br><input type="text" name="eagle_forms_fields[' + index + '][label]" /></label>',
                '       </p>',
                '       <p>',
                '           <label>Field Name (unique)<br><input type="text" name="eagle_forms_fields[' + index + '][name]" value="field_' + (index + 1) + '" /></label>',
                '       </p>',
                '       <p>',
                '           <label>Type<br><select name="eagle_forms_fields[' + index + '][type]">' + options + '</select></label>',
                '       </p>',
                '       <p>',
                '           <label>Placeholder / Options<br><textarea name="eagle_forms_fields[' + index + '][placeholder]" rows="3"></textarea></label>',
                '       </p>',
                '       <p>',
                '           <label><input type="checkbox" name="eagle_forms_fields[' + index + '][required]" value="1" /> Required field</label>',
                '       </p>',
                '       <button type="button" class="button-link-delete eagle-forms-remove-field">Remove</button>',
                '   </div>',
                '</li>'
            ].join('');
        }
    };

    $(function(){
        if ($('#eagle-forms-builder').length) {
            builder.init();
        }
    });
})(jQuery);
