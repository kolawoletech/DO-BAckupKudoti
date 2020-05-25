let Fields = null;
let Settings = null;
let Actions = null;

let getProductsOptions = (input) => {
    return fetch( fpf_admin.rest_url + 'flexible_product_fields/v1/products/' + fpf_admin.rest_param + 'search=' + input + '&_wp_rest_nonce=' + fpf_admin.rest_nonce,
        ).then((response) => {
            return response.json();
        }).then((json) => {
            return { options: json };
        });
}

let getCategoriesOptions = (input) => {
    return fetch( fpf_admin.rest_url + 'flexible_product_fields/v1/categories/' + fpf_admin.rest_param + 'search=' + input + '&_wp_rest_nonce=' + fpf_admin.rest_nonce,
    ).then((response) => {
        return response.json();
    }).then((json) => {
        return { options: json };
    });
}


let { SortableContainer, SortableElement, SortableHandle, arrayMove } = window.SortableHOC;

const DragHandle = SortableHandle(() => <span className="fpf-drag"><span className="dashicons dashicons-menu"></span></span>);


const SortableItemOptionsNoPrice = SortableElement (
    ( {value, i, onRemove} ) =>
        <FPF_Option_No_Price
            value={value}
            i={i}
            onRemove={onRemove}
        />
);

const SortableItemOptions = SortableElement (
    ( {value, i, onRemove} ) =>
            <FPF_Option
                value={value}
                i={i}
                onRemove={onRemove}
            />
);

const SortableItemRules = SortableElement (
    ( {value, i, onRemove} ) =>
        <FPF_Rule
            value={value}
            i={i}
            onRemove={onRemove}
        />
);

const SortableListRules = SortableContainer(({items,onRemove}) => {
    return (
        <tbody>
        {items.map((value, index) =>
            <SortableItemRules
                key={`item-${value.id}`}
                index={index}
                i={index}
                value={value}
                onRemove={onRemove}
            />
        )}
        </tbody>
    );
});


const SortableListOptionsNoPrice = SortableContainer(({items,onRemove}) => {
    return (
        <tbody>
        {items.map((value, index) =>
            <SortableItemOptionsNoPrice
                key={`item-${value.id}`}
                index={index}
                i={index}
                value={value}
                onRemove={onRemove}
            />
        )}
        </tbody>
    );
});

const SortableListOptions = SortableContainer(({items,onRemove}) => {
    return (
        <tbody>
            {items.map((value, index) =>
                <SortableItemOptions
                    key={`item-${value.id}`}
                    index={index}
                    i={index}
                    value={value}
                    onRemove={onRemove}
                />
            )}
        </tbody>
    );
});

const SortableItem = SortableElement (
    ( {value, i, onRemove} ) =>
        <FPF_Field
            i={i}
            data={value}
            onRemove={onRemove}
        />
);

const SortableList = SortableContainer(({items,onRemove}) => {
    return (
        <div>
            {items.map((value, index) =>
                <SortableItem
                    key={`item-${value.id}`}
                    index={index}
                    i={index}
                    value={value}
                    onRemove={onRemove}
                />
            )}
        </div>
    );
});

class SortableComponent extends React.Component {

    constructor( props ) {
        super( props );
        this.state = {
            items: props.items
        };
        this.onRemove = this.onRemove.bind(this);
    }

    onRemove( i ) {
        let items = this.state.items;
        for ( var n = 0 ; n < items.length ; n++) {
            if ( items[n].id == i ) {
                var removedObject = items.splice(n,1);
                removedObject = null;
                break;
            }
        }
        this.setState({items: items});
        Fields.setState( { fields: this.state.items } );
    }

    onSortEnd = ( {oldIndex, newIndex} ) => {
        this.setState({
            items: arrayMove( this.state.items, oldIndex, newIndex )
        });
        Fields.setState( { fields: this.state.items } );
        fpf_settings.fields = this.state.items;
    };

    render() {
        return (
            <SortableList
                items={this.state.items}
                onSortEnd={this.onSortEnd}
                useDragHandle={true}
                onRemove={this.onRemove}
            />
        )
    }
}



class FPF_Option_No_Price extends React.Component {

    constructor(props) {
        super( props );
        this.state = {
            data: props.value,
        };

        this.onChangeValue = this.onChangeValue.bind(this);
        this.onChangeLabel = this.onChangeLabel.bind(this);
    }

    onChangeValue(event) {
        let data2 = this.state.data;
        data2.value = event.target.value;
        this.setState( { data: data2 } );
    }

    onChangeLabel(event) {
        let data2 = this.state.data;
        data2.label = event.target.value;
        this.setState( { data: data2 } );
    }

    render() {
        return (
            <tr>
                <td className="fpf-row-handle">
                    <DragHandle/>
                </td>
                <td>
                    <input type="text" className="fpf-field-option-value" name="option_value" value={this.state.data.value} onChange={this.onChangeValue}/>
                </td>
                <td>
                    <input type="text" className="fpf-field-option-label" name="option_label" value={this.state.data.label} onChange={this.onChangeLabel}/>
                </td>
                <td>
                    {fpf_admin.price_adv} <a href={fpf_admin.price_adv_link}>{fpf_admin.price_adv_link_text}</a>
                </td>
                <td className="fpf-row-handle">
                    <a className="fpf-option-delete dashicons dashicons-trash" onClick={() => this.props.onRemove(this.state.data.id)}> </a>
                </td>
            </tr>
        )
    }

}


class FPF_Option extends React.Component {

    constructor(props) {
        super( props );
        this.state = {
            data: props.value,
        };

        this.onChangeValue = this.onChangeValue.bind(this);
        this.onChangeLabel = this.onChangeLabel.bind(this);
        this.onChangePriceType = this.onChangePriceType.bind(this);
        this.onChangePrice = this.onChangePrice.bind(this);
    }

    onChangeValue(event) {
        let data2 = this.state.data;
        data2.value = event.target.value;
        this.setState( { data: data2 } );
    }

    onChangeLabel(event) {
        let data2 = this.state.data;
        data2.label = event.target.value;
        this.setState( { data: data2 } );
    }

    onChangePriceType(event) {
        let data2 = this.state.data;
        data2.price_type = event.target.value;
        this.setState( { data: data2 } );
    }

    onChangePriceType2(val) {
        let data2 = this.state.data;
        data2.price_type = val.value;
        this.setState( { data: data2 } );
    }

    onChangePrice(event) {
        let data2 = this.state.data;
        data2.price = event.target.value;
        this.setState( { data: data2 } );
    }

    render() {
        return (
            <tr>
                <td className="fpf-row-handle">
                    <DragHandle/>
                </td>
                <td>
                    <input type="text" className="fpf-field-option-value" name="option_value" value={this.state.data.value} onChange={this.onChangeValue}/>
                </td>
                <td>
                    <input type="text" className="fpf-field-option-label" name="option_label" value={this.state.data.label} onChange={this.onChangeLabel}/>
                </td>
                <td>
                    <select name="price_type" onChange={this.onChangePriceType} value={this.state.data.price_type}>
                        {
                            fpf_price_type_options.map(function (item) {
                                return <option key={item.value} value={item.value}>{item.label}</option>;
                            })
                        }
                    </select>
                </td>
                <td>
                    <input type="number" className="fpf-field-price" name="field_price" value={this.state.data.price} onChange={this.onChangePrice} step="0.01" />
                </td>
                <td className="fpf-row-handle">
                    <a className="fpf-option-delete dashicons dashicons-trash" onClick={() => this.props.onRemove(this.state.data.id)}> </a>
                </td>
            </tr>
        )
    }

}

class FPF_Rule extends React.Component {

    constructor(props) {
        super( props );
        let fields = [];
        let values = [];
        jQuery.each(fpf_settings['fields'], function( index, value) {
            if ( value.type == 'select' || value.type == 'radio' ) {
                fields.push( value );
                if ( props.value.field == value.id ) {
                    jQuery.each(value.options, function (option_index, option_value) {
                        values.push(option_value);
                    });
                }
            }
            if ( value.type == 'checkbox' ) {
                fields.push( value );
                if ( props.value.field == value.id ) {
                    values.push({value: 'checked', label: fpf_admin.checked_label});
                    values.push({value: 'unchecked', label: fpf_admin.unchecked_label});
                }
            }
        });
        this.state = {
            data: props.value,
            fields: fields,
            field_values: values,
        };

        this.onChangeField = this.onChangeField.bind(this);
        this.onChangeCompare = this.onChangeCompare.bind(this);
        this.onChangeFieldValue = this.onChangeFieldValue.bind(this);
    }

    onChangeField(event) {
        let data2 = this.state.data;
        data2.field = event.target.value;
        data2.field_value = '';
        let fields = [];
        let values = [];
        jQuery.each(fpf_settings['fields'], function( index, value) {
            if ( value.type == 'select' || value.type == 'radio' ) {
                fields.push( value );
                if ( data2.field == value.id ) {
                    jQuery.each(value.options, function (option_index, option_value) {
                        values.push(option_value);
                    });
                }
            }
            if ( value.type == 'checkbox' ) {
                fields.push( value );
                if ( data2.field == value.id ) {
                    values.push( { value: 'checked', label: fpf_admin.checked_label } );
                    values.push( { value: 'unchecked', label: fpf_admin.unchecked_label } );
                }
            }
        });
        this.setState( {
            data: data2,
            fields: fields,
            field_values: values,
        } );
        this.forceUpdate();
    }

    onChangeCompare(event) {
        let data2 = this.state.data;
        data2.compare = event.target.value;
        this.setState( { data: data2 } );
    }

    onChangeFieldValue(event) {
        let data2 = this.state.data;
        data2.field_value = event.target.value;
        this.setState( { data: data2 } );
    }


    render() {
        return (
            <tr>
                <td className="fpf-row-handle">
                    <DragHandle/>
                </td>
                <td>
                    <select name="field_logic_field" onChange={this.onChangeField} value={this.state.data.field}>
                        <option value="">{fpf_admin.logic_select_field}</option>
                        {this.state.fields.map((value, index) =>
                            <option key={index} value={value.id}>{value.title}</option>
                        )}
                    </select>
                </td>
                <td>
                    <select name="field_logic_compare" onChange={this.onChangeCompare} value={this.state.data.compare}>
                        <option value="is">{fpf_admin.logic_compare_is}</option>
                        <option value="is_not">{fpf_admin.logic_compare_is_not}</option>
                    </select>
                </td>
                <td>
                    <select name="field_logic_field_value" onChange={this.onChangeFieldValue} value={this.state.data.field_value}>
                        <option value="">{fpf_admin.logic_select_field_value}</option>
                        {this.state.field_values.map((value, index) =>
                            <option key={index} value={value.value}>{value.label}</option>
                        )}
                    </select>
                </td>
                <td className="fpf-row-handle">
                    <a className="fpf-rule-delete dashicons dashicons-trash" onClick={() => this.props.onRemove(this.state.data.id)}> </a>
                </td>
            </tr>
        )
    }

}

class FPF_Field extends React.Component {

    constructor(props) {
        super( props );
        this.state = {
            data: props.data,
            i: props.i,
            key: props.key,
            other: props.other,
            id: props.data.id,
        };
        if ( typeof this.state.data.options == 'undefined' ) {
            this.state.data.options = [];
        }
        if ( typeof this.state.data.logic_rules == 'undefined' ) {
            this.state.data.logic_rules = [];
        }
        this.onChangeTitle = this.onChangeTitle.bind(this);
        this.onChangeType = this.onChangeType.bind(this);
        this.onChangeRequired = this.onChangeRequired.bind(this);
        this.onChangeMaxLength = this.onChangeMaxLength.bind(this);
        this.onChangeCssClass = this.onChangeCssClass.bind(this);
        this.onChangePlaceholder = this.onChangePlaceholder.bind(this);
        this.onChangeValue = this.onChangeValue.bind(this);
        this.onChangePriceType = this.onChangePriceType.bind(this);
        this.onChangePrice = this.onChangePrice.bind(this);
        this.onChangeDateFormat = this.onChangeDateFormat.bind(this);
        this.onChangeDaysBefore = this.onChangeDaysBefore.bind(this);
        this.onChangeDaysAfter = this.onChangeDaysAfter.bind(this);

        this.onChangeLogic = this.onChangeLogic.bind(this);
        this.onChangeLogicOperator = this.onChangeLogicOperator.bind(this);

        this.onClickToggleDisplay = this.onClickToggleDisplay.bind(this);
        this.handleMouseEnter = this.handleMouseEnter.bind(this);
        this.handleMouseLeave = this.handleMouseLeave.bind(this);

        this.handleAddOption = this.handleAddOption.bind(this);
        this.onRemoveOption = this.onRemoveOption.bind(this);

        this.handleAddRule = this.handleAddRule.bind(this);
        this.onRemoveRule = this.onRemoveRule.bind(this);

    }

    onChangeTitle(event) {
        let data2 = this.state.data;
        data2.title = event.target.value;
        this.setState( { data: data2 } );
    }

    onChangeType(event) {
        let data2 = this.state.data;
        data2.type = event.target.value;
        this.setState( { data: data2 } );
    }

    onChangeType2(val) {
        let data2 = this.state.data;
        data2.type = val.value;
        this.setState( { data: data2 } );
    }

    onChangeRequired(event) {
        let data2 = this.state.data;
        data2.required = !data2.required;
        this.setState( { data: data2 } );
    }

    onChangeMaxLength(event) {
        let data2 = this.state.data;
        data2.max_length = event.target.value;
        this.setState( { data: data2 } );
    }

    onChangeCssClass(event) {
        let data2 = this.state.data;
        data2.css_class = event.target.value;
        this.setState( { data: data2 } );
    }

    onChangePlaceholder(event) {
        let data2 = this.state.data;
        data2.placeholder = event.target.value;
        this.setState( { data: data2 } );
    }

    onChangeValue(event) {
        let data2 = this.state.data;
        data2.value = event.target.value;
        this.setState( { data: data2 } );
    }

    onChangePriceType(event) {
        let data2 = this.state.data;
        data2.price_type = event.target.value;
        this.setState( { data: data2 } );
    }

    onChangePriceType2(val) {
        let data2 = this.state.data;
        data2.price_type = val.value;
        this.setState( { data: data2 } );
    }

    onChangePrice(event) {
        let data2 = this.state.data;
        data2.price = event.target.value;
        this.setState( { data: data2 } );
    }

    onChangeDateFormat(event) {
        let data2 = this.state.data;
        data2.date_format = event.target.value;
        this.setState( { data: data2 } );
    }

    onChangeDaysBefore(event) {
        let data2 = this.state.data;
        data2.days_before = event.target.value;
        this.setState( { data: data2 } );
    }

    onChangeDaysAfter(event) {
        let data2 = this.state.data;
        data2.days_after = event.target.value;
        this.setState( { data: data2 } );
    }

    onChangeLogic(event) {
        let data2 = this.state.data;
        data2.logic = !data2.logic;
        this.setState( { data: data2 } );
    }

    onChangeLogicOperator(event) {
        let data2 = this.state.data;
        data2.logic_operator = event.target.value;
        this.setState( { data: data2 } );
    }

    onClickToggleDisplay(event) {
        let data2 = this.state.data;
        data2.display = !data2.display;
        this.setState( { data: data2 } );
    }

    handleMouseEnter(event) {
        this.setState( { mouseEnter: true } );
    }

    handleMouseLeave(event) {
        this.setState( { mouseEnter: false } );
    }

    handleAddOption(event) {

        event.preventDefault();

        let data = this.state.data;
        if ( typeof data.options == 'undefined' ) {
            data.options = [];
        }

        var key = "fpf_"+Math.floor((Math.random() * 10000000) + 1);
        var option = { id: key, value: '', label: '', price_type: fpf_price_type_options[0].value, price: '' };

        data.options.push( option );

        this.setState(
            { data: data }
        );
    }

    onSortEndOptions = ( {oldIndex, newIndex} ) => {
        let data = this.state.data;
        data.options = arrayMove( data.options, oldIndex, newIndex );
        this.setState({
            data: data
        });

    };

    onRemoveOption( i ) {
        let data = this.state.data;
        for ( var n = 0 ; n < data.options.length ; n++) {
            if ( data.options[n].id == i ) {
                var removedObject = data.options.splice(n,1);
                removedObject = null;
                break;
            }
        }
        this.setState({data: data});
    }


    handleAddRule(event) {

        event.preventDefault();

        let data = this.state.data;
        if ( typeof data.logic_rules == 'undefined' ) {
            data.logic_rules = [];
        }

        var key = "fpf_"+Math.floor((Math.random() * 10000000) + 1);
        var rule = { id: key, field: '', compare: '', field_value: '' };

        data.logic_rules.push( rule );

        this.setState(
            { data: data }
        );
    }

    onSortEndRules = ( {oldIndex, newIndex} ) => {
        let data = this.state.data;
        data.logic_rules = arrayMove( data.logic_rules, oldIndex, newIndex );
        this.setState({
            data: data
        });

    };

    onRemoveRule( i ) {
        let data = this.state.data;
        for ( var n = 0 ; n < data.logic_rules.length ; n++) {
            if ( data.logic_rules[n].id == i ) {
                var removedObject = data.logic_rules.splice(n,1);
                removedObject = null;
                break;
            }
        }
        this.setState({data: data});
    }


    render() {
        const showHide = {
            'display': this.state.data.display ? 'block' : 'none'
        };
        const required = this.state.data.required ? '*' : '';
        const toggleClass = this.state.data.display ? "open" : "closed";

        return (
            <div className={"fpf-field-object " + toggleClass}>
                <div className="fpf-field-title-row" onClick={this.onClickToggleDisplay} onMouseEnter={this.handleMouseEnter} onMouseLeave={this.handleMouseLeave}>
                    <div className="fpf-field-sort">
                        <DragHandle/>
                    </div>
                    <div className="fpf-field-title">
                        <strong>{this.state.data.title} {required}</strong>
                        { this.state.mouseEnter ?
                            <span className="fpf-row-actions">
                                <span className="fpf-edit-action">{fpf_admin.edit_label}</span>
                                &nbsp;|&nbsp;
                                <span className="fpf-delete-action" onClick={() => this.props.onRemove(this.state.data.id)}>{fpf_admin.delete_label}{this.state.index}</span>
                            </span>
                            :
                            <span className="fpf-row-actions">
                                &nbsp;
                            </span>
                        }
                    </div>
                    <div className="fpf-field-type">{fpf_field_types[this.state.data.type]['label']}</div>
                </div>
                <div className="fpf-field-inputs" style={showHide}>
                    <table className="fpf-table">
                        <tbody>
                            <tr className="fpf-field">
                                <td className="fpf-label">
                                    <label htmlFor={"field_title_" + this.state.id}>{fpf_admin.field_title_label}</label>
                                </td>

                                <td className="fpf-input">
                                    <input type="text" className="fpf-field-title" id={"field_title_" + this.state.id} name="field_title" value={this.state.data.title} onChange={this.onChangeTitle}/>
                                </td>
                            </tr>

                            <tr className="fpf-field">
                                <td className="fpf-label">
                                    <label htmlFor={"field_type_" + this.state.id}>{fpf_admin.field_type_label}</label>
                                </td>

                                <td className="fpf-input">
                                    <select id={"field_type_" + this.state.id} name="field_type" onChange={this.onChangeType} value={this.state.data.type}>
                                        {
                                            fpf_field_type_options.map(function (item) {
                                                return <option key={item.value} value={item.value}>{item.label}</option>;
                                            })
                                        }
                                    </select>
                                    {  fpf_field_types[this.state.data.type]['is_available'] ?
                                        null
                                        :
                                        <span>
                                            <br/><br/>
                                            {fpf_admin.fields_adv} <a href={fpf_admin.fields_adv_link}>{fpf_admin.fields_adv_link_text}</a>
                                        </span>
                                    }
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    {  fpf_field_types[this.state.data.type]['is_available'] ?
                        <table className="fpf-table fpf-table-field-properies">
                            <tbody>
                            {  fpf_field_types[this.state.data.type]['has_required'] ?
                                <tr className="fpf-field">
                                    <td className="fpf-label">
                                        <label
                                            htmlFor={"field_required_" + this.state.id}>{fpf_admin.field_required_label}</label>
                                    </td>

                                    <td className="fpf-input">
                                        <input
                                            name="field_required"
                                            className="fpf-field-required"
                                            id={"field_required_" + this.state.id}
                                            type="checkbox"
                                            checked={this.state.data.required}
                                            onChange={this.onChangeRequired}
                                        />
                                    </td>
                                </tr>
                                : null
                            }

                            {  fpf_field_types[this.state.data.type]['has_max_length'] ?
                                <tr className="fpf-field">
                                    <td className="fpf-label">
                                        <label
                                            htmlFor={"field_max_length_" + this.state.id}>{fpf_admin.field_max_length_label}</label>
                                    </td>

                                    <td className="fpf-input">
                                        <input
                                            type="number"
                                            className="fpf-field-max-length"
                                            id={"field_max_length_" + this.state.id}
                                            name="field_max_length"
                                            value={this.state.data.max_length}
                                            onChange={this.onChangeMaxLength}
                                            step="1"
                                            min="1"
                                        />
                                    </td>
                                </tr>
                                : null
                            }

                            {  fpf_field_types[this.state.data.type]['has_placeholder'] ?
                                <tr className="fpf-field">
                                    <td className="fpf-label">
                                        <label
                                            htmlFor={"field_placeholder_" + this.state.id}>{fpf_admin.field_placeholder_label}</label>
                                    </td>

                                    <td className="fpf-input">
                                        <input
                                            type="text"
                                            className="fpf-field-placeholder"
                                            id={"field_placeholder_" + this.state.id}
                                            name="field_placeholder"
                                            value={this.state.data.placeholder}
                                            onChange={this.onChangePlaceholder}
                                        />
                                    </td>
                                </tr>
                                : null
                            }

                            {  fpf_field_types[this.state.data.type]['has_value'] ?
                                <tr className="fpf-field">
                                    <td className="fpf-label">
                                        <label
                                            htmlFor={"field_value_" + this.state.id}>{fpf_admin.field_value_label}</label>
                                    </td>

                                    <td className="fpf-input">
                                        <input type="text" className="fpf-field-value"
                                               id={"field_value_" + this.state.id} name="field_value"
                                               value={this.state.data.value} onChange={this.onChangeValue}/>
                                    </td>
                                </tr>
                                : null
                            }

                            <tr className="fpf-field">
                                <td className="fpf-label">
                                    <label
                                        htmlFor={"field_css_class_" + this.state.id}>{fpf_admin.field_css_class_label}</label>
                                </td>

                                <td className="fpf-input">
                                    <input type="text" className="fpf-field-css-class"
                                           id={"field_css_class_" + this.state.id} name="field_css_class"
                                           value={this.state.data.css_class} onChange={this.onChangeCssClass}/>
                                </td>
                            </tr>

                            {  this.state.data.type == 'fpfdate' ?

                                <tr className="fpf-field">
                                    <td className="fpf-label">
                                        <label
                                            htmlFor={"field_date_format_" + this.state.id}>{fpf_admin.field_date_format_label}
                                        </label>
                                    </td>
                                    <td className="fpf-input">
                                        <input
                                            type="text"
                                            className="fpf-field-date-format"
                                            id={"field_date_format_" + this.state.id}
                                            name="field_date_format"
                                            value={this.state.data.date_format}
                                            onChange={this.onChangeDateFormat}
                                        />
                                    </td>
                                </tr>

                                :
                                null
                            }

                            {  this.state.data.type == 'fpfdate' ?

                                <tr className="fpf-field">
                                    <td className="fpf-label">
                                        <label
                                            htmlFor={"field_days_before_" + this.state.id}>{fpf_admin.field_days_before_label}
                                        </label>
                                    </td>
                                    <td className="fpf-input">
                                        <input
                                            type="number"
                                            className="fpf-field-days-before"
                                            id={"field_days_before_" + this.state.id}
                                            name="field_days_before"
                                            value={this.state.data.days_before}
                                            onChange={this.onChangeDaysBefore}
                                            step="1"
                                        />
                                    </td>
                                </tr>

                                :
                                null
                            }

                            {  this.state.data.type == 'fpfdate' ?

                                <tr className="fpf-field">
                                    <td className="fpf-label">
                                        <label
                                            htmlFor={"field_days_after_" + this.state.id}>{fpf_admin.field_days_after_label}
                                        </label>
                                    </td>
                                    <td className="fpf-input">
                                        <input
                                            type="number"
                                            className="fpf-field-days-after"
                                            id={"field_days_after_" + this.state.id}
                                            name="field_days_after"
                                            value={this.state.data.days_after}
                                            onChange={this.onChangeDaysAfter}
                                            step="1"
                                        />
                                    </td>
                                </tr>

                                :
                                null
                            }

                            {  fpf_field_types[this.state.data.type]['has_price'] ?

                                <tr className="fpf-field">
                                    <td className="fpf-label">
                                        <label
                                            htmlFor={"field_price_type_" + this.state.id}>{fpf_admin.field_price_type_label}</label>
                                    </td>

                                    <td className="fpf-input">
                                        <select name="price_type" onChange={this.onChangePriceType}
                                                value={this.state.data.price_type}>
                                            {
                                                fpf_price_type_options.map(function (item) {
                                                    return <option key={item.value}
                                                                   value={item.value}>{item.label}</option>;
                                                })
                                            }
                                        </select>
                                    </td>
                                </tr>

                                :
                                null
                            }

                            {  fpf_field_types[this.state.data.type]['price_not_available'] ?

                                <tr className="fpf-field">
                                    <td className="fpf-label">
                                        <label
                                            htmlFor={"field_price_type_" + this.state.id}>{fpf_admin.field_price_type_label}</label>
                                    </td>

                                    <td className="fpf-input">
                                        {fpf_admin.price_adv} <a href={fpf_admin.price_adv_link}>{fpf_admin.price_adv_link_text}</a>
                                    </td>
                                </tr>

                                :
                                null
                            }

                            {  fpf_field_types[this.state.data.type]['has_price'] ?

                                <tr className="fpf-field">
                                    <td className="fpf-label">
                                        <label
                                            htmlFor={"field_price_" + this.state.id}>{fpf_admin.field_price_label}</label>
                                    </td>

                                    <td className="fpf-input">
                                        <input
                                            type="number"
                                            className="fpf-field-price"
                                            id={"field_price_" + this.state.id}
                                            name="field_price"
                                            value={this.state.data.price}
                                            onChange={this.onChangePrice}
                                            step="0.01"
                                        />
                                    </td>
                                </tr>

                                :
                                null
                            }

                            {  fpf_field_types[this.state.data.type]['price_not_available'] ?

                                <tr className="fpf-field">
                                    <td className="fpf-label">
                                        <label
                                            htmlFor={"field_price_" + this.state.id}>{fpf_admin.field_price_label}</label>
                                    </td>

                                    <td className="fpf-input">
                                        {fpf_admin.price_adv} <a href={fpf_admin.price_adv_link}>{fpf_admin.price_adv_link_text}</a>
                                    </td>
                                </tr>

                                :
                                null
                            }

                            { fpf_field_types[this.state.data.type]['has_options'] ?
                                <tr className="fpf-field">
                                    <td className="fpf-label">
                                        <label>{fpf_admin.field_options_label}</label>
                                    </td>
                                    <td className="fpf-input">
                                        <table className="fpf-table fpf-options">
                                            <thead>
                                            <tr>
                                                <th className="fpf-row-handle"></th>
                                                <th>{fpf_admin.option_value_label}</th>
                                                <th>{fpf_admin.option_label_label}</th>
                                                { fpf_field_types[this.state.data.type]['has_price_in_options'] ?
                                                    <th className="fpf-row-price">{fpf_admin.option_price_type_label}</th>
                                                    :
                                                    null
                                                }
                                                { fpf_field_types[this.state.data.type]['has_price_in_options'] ?
                                                    <th>{fpf_admin.option_price_label}</th>
                                                    :
                                                    null
                                                }
                                                { fpf_field_types[this.state.data.type]['price_not_available_in_options'] ?
                                                    <th>{fpf_admin.option_price_label}</th>
                                                    :
                                                    null
                                                }
                                                <th className="fpf-row-handle"></th>
                                            </tr>
                                            </thead>

                                            {fpf_field_types[this.state.data.type]['has_price_in_options'] ?
                                                <SortableListOptions
                                                    items={this.state.data.options}
                                                    onRemove={this.onRemoveOption}
                                                    useDragHandle={true}
                                                    onSortEnd={this.onSortEndOptions}
                                                    helperClass="fpf-option-drag"
                                                />
                                                :
                                                null
                                            }
                                            { fpf_field_types[this.state.data.type]['price_not_available_in_options'] ?
                                                <SortableListOptionsNoPrice
                                                    items={this.state.data.options}
                                                    onRemove={this.onRemoveOption}
                                                    useDragHandle={true}
                                                    onSortEnd={this.onSortEndOptions}
                                                    helperClass="fpf-option-drag"
                                                />
                                                :
                                                null
                                            }

                                        </table>
                                        <button className="fpf-add-option button button-primary"
                                                onClick={this.handleAddOption}>
                                            {fpf_admin.add_option_label}
                                        </button>
                                    </td>
                                </tr>
                                :
                                null
                            }

                            {  fpf_field_types[this.state.data.type]['has_logic'] ?
                                <tr className="fpf-field">
                                    <td className="fpf-label">
                                        <label
                                            htmlFor={"field_logic_" + this.state.id}
                                        >
                                            {fpf_admin.field_logic_label}
                                        </label>
                                    </td>
                                    <td className="fpf-input">
                                        <input
                                            name="field_logic"
                                            className="fpf-field-logic"
                                            id={"field_logic_" + this.state.id}
                                            type="checkbox"
                                            checked={this.state.data.logic}
                                            onChange={this.onChangeLogic}
                                        />
                                    </td>
                                </tr>
                                :
                                null
                            }

                            {  fpf_field_types[this.state.data.type]['logic_not_available'] ?

                                <tr className="fpf-field">
                                    <td className="fpf-label">
                                        <label
                                            htmlFor={"field_logic_" + this.state.id}
                                        >
                                            {fpf_admin.field_logic_label}
                                        </label>
                                    </td>

                                    <td className="fpf-input">
                                        {fpf_admin.logic_adv} <a href={fpf_admin.logic_adv_link}>{fpf_admin.logic_adv_link_text}</a>
                                    </td>
                                </tr>

                                :
                                null
                            }

                            {  fpf_field_types[this.state.data.type]['has_logic'] && this.state.data.logic ?
                                <tr className="fpf-field">
                                    <td className="fpf-label">
                                        <label
                                            htmlFor={"field_logic_operator" + this.state.id}
                                        >
                                            {fpf_admin.logic_label_operator}
                                        </label>
                                    </td>
                                    <td className="fpf-input">
                                        <select name="field_logic_operator" onChange={this.onChangeLogicOperator} value={this.state.data.logic_operator}>
                                            <option value="or">{fpf_admin.logic_label_operator_or}</option>
                                            <option value="and">{fpf_admin.logic_label_operator_and}</option>
                                        </select>
                                    </td>
                                </tr>
                                :
                                null
                            }

                            {  fpf_field_types[this.state.data.type]['has_logic'] && this.state.data.logic ?
                                <tr className="fpf-field">
                                    <td className="fpf-label">
                                        <label
                                            htmlFor={"field_logic_rules" + this.state.id}
                                        >
                                            {fpf_admin.logic_label_rules}
                                        </label>
                                    </td>
                                    <td className="fpf-input">
                                        <table className="fpf-table fpf-options">
                                            <thead>
                                            <tr>
                                                <th className="fpf-row-handle"></th>
                                                <th>{fpf_admin.logic_label_field}</th>
                                                <th>{fpf_admin.logic_label_compare}</th>
                                                <th>{fpf_admin.logic_label_value}</th>
                                                <th className="fpf-row-handle"></th>
                                            </tr>
                                            </thead>

                                            <SortableListRules
                                                items={this.state.data.logic_rules}
                                                onRemove={this.onRemoveRule}
                                                useDragHandle={true}
                                                onSortEnd={this.onSortEndRules}
                                                helperClass="fpf-option-drag"
                                            />

                                        </table>
                                        <button className="fpf-add-option button button-primary"
                                                onClick={this.handleAddRule}>
                                            {fpf_admin.add_rule_label}
                                        </button>
                                    </td>
                                </tr>
                                :
                                null
                            }

                            </tbody>
                        </table>
                        : null
                    }
                </div>
            </div>
        );
    }
}

class FPF_Fields extends React.Component {

    constructor( props ) {
        super( props );
        this.state = {
            fields: props.fields
        }
    }

    render() {
        return (
            <div>
                <SortableComponent items={this.state.fields} />
            </div>
        );
    }
}

class FPF_Button_Add extends React.Component {

    constructor( props ) {
        super( props );
        this.handleClick = this.handleClick.bind(this);
    }

    handleClick( e ) {
        e.preventDefault();
        var key = "fpf_"+Math.floor((Math.random() * 10000000) + 1);
        var data = {
            id: key,
            title: fpf_admin.new_field_title,
            type: 'text',
            max_length: '',
            required: false,
            placeholder: '',
            css_class: '',
            price: '',
            price_type: fpf_price_type_options[0].value,
            date_format: 'dd.mm.yy',
            days_before: '',
            days_after: '',
            logic: false,
            logic_operator: 'or',
            display: true,
        };
        this.props.addField(data);
    }

    render() {
        return (
            <button className="fpf-button-add button button-primary" onClick={this.handleClick}>
                {fpf_admin.add_field_label}
            </button>
        )
    }
}

class FPF_Fields_Container extends React.Component {

    constructor(props) {
        super(props);
        Fields = this;
        this.state = {
            fields: fpf_settings.fields,
            assign_to: fpf_settings.assign_to.value
        };
        this.addField = this.addField.bind(this);
        this.changeAssignTo = this.changeAssignTo.bind(this);
    }

    changeAssignTo( assign_to ) {
        this.setState(
            { assign_to: assign_to }
        );
    }

    addField( data ) {
        let fields = this.state.fields;
        fields.push( data );
        this.setState(
            { fields: fields }
        );
    }

    render() {
        return (
            <div className="fpf-fields-set">
                <div>
                    <FPF_Fields fields={this.state.fields} ref="fields"/>
                    <div className="fpf-footer">
                        < FPF_Button_Add addField={this.addField}/>
                    </div>
                </div>
            </div>
        );
    }
}

class FPF_Settings_Field extends React.Component {

    constructor(props) {
        super( props );
        this.state = {
            assign_to: props.assign_to,
            section: props.section,
            products: props.products,
            categories: props.categories,
            menu_order: props.menu_order,
        }
        if ( fpf_assign_to_values[this.state.assign_to.value]['is_available'] ) {
            document.getElementById('fpf_fields').style.display = 'block';
        }
        else {
            document.getElementById('fpf_fields').style.display = 'none';
        }
        this.assignToChange = this.assignToChange.bind(this);
        this.sectionChange = this.sectionChange.bind(this);
        this.productsChange = this.productsChange.bind(this);
        this.categoriesChange = this.categoriesChange.bind(this);
        this.menuOrderChange = this.menuOrderChange.bind(this);

    }

    assignToChange(event) {
        var assign_to = this.state.assign_to;
        assign_to.value = event.target.value;
        this.setState( { assign_to: assign_to } );
        fpf_settings.assign_to = assign_to;
        if ( fpf_assign_to_values[this.state.assign_to.value]['is_available'] ) {
            document.getElementById('fpf_fields').style.display = 'block';
        }
        else {
            document.getElementById('fpf_fields').style.display = 'none';
        }
    }

    assignToChange2(val) {
        var assign_to = this.state.assign_to;
        assign_to.value = val.value;
        this.setState( { assign_to: assign_to } );
    }

    sectionChange(event) {
        var section = this.state.section;
        section.value = event.target.value;
        this.setState( { section: section } );
    }

    menuOrderChange(event) {
        var menu_order = this.state.menu_order;
        menu_order.value = event.target.value;
        this.setState( { menu_order: menu_order } );
    }

    sectionChange2(val) {
        var section = this.state.section;
        section.value = val.value;
        this.setState( { section: section } );
    }

    productsChange(val) {
        var products2 = this.state.products;
        products2.value = val;
        this.setState( { products: products2 } );
    }

    categoriesChange(val) {
        var categories2 = this.state.categories;
        categories2.value = val;
        this.setState( { categories: categories2 } );
    }

    render() {
        return (
            <table className="fpf-table">
                <tbody>
                    <tr className="fpf-field fpf-field-section">
                        <td className="fpf-label">
                            <label htmlFor="">{fpf_admin.section_label}</label>
                        </td>

                        <td className="fpf-input">
                            <select name="section" onChange={this.sectionChange} value={this.state.section.value}>
                                {
                                    fpf_sections_options.map(function (item) {
                                        return <option key={item.value} value={item.value}>{item.label}</option>;
                                    })
                                }
                            </select>
                        </td>
                    </tr>
                    <tr className="fpf-field fpf-field-assign-to-label">
                        <td className="fpf-label">
                            <label htmlFor="">{fpf_admin.assign_to_label}</label>
                        </td>

                        <td className="fpf-input">
                            <select name="assing_to" onChange={this.assignToChange} value={this.state.assign_to.value}>
                                {
                                    fpf_assign_to_options.map(function (item) {
                                        return <option key={item.value}  disabled={item.disabled} value={item.value}>{item.label}</option>;
                                    })
                                }
                            </select>
                            {  fpf_assign_to_values[this.state.assign_to.value]['is_available'] ?
                                null
                                :
                                <span>
                                    <br/><br/>
                                    {fpf_admin.assign_to_adv} <a href={fpf_admin.assign_to_adv_link}>{fpf_admin.assign_to_adv_link_text}</a>
                                </span>
                            }
                        </td>
                    </tr>
                    { this.state.assign_to.value == 'product' && fpf_assign_to_values[this.state.assign_to.value]['is_available'] ?
                        <tr className="fpf-field fpf-field-product-label">
                            <td className="fpf-label">
                                <label htmlFor="">{fpf_admin.products_label}</label>
                            </td>

                            <td className="fpf-input">
                                {  fpf_assign_to_values[this.state.assign_to.value]['is_available'] ?
                                    <Select.Async
                                        name="products"
                                        value={this.state.products.value}
                                        onChange={this.productsChange}
                                        loadOptions={getProductsOptions}
                                        placeholder={fpf_admin.select_placeholder}
                                        //simpleValue={true}
                                        searchPromptText={fpf_admin.select_type_to_search}
                                        multi={true}
                                        autoload={false}
                                        ref="products"
                                    />
                                    :
                                    <span>
                                        {fpf_admin.assign_to_adv} <a href={fpf_admin.assign_to_adv_link}>{fpf_admin.assign_to_adv_link_text}</a>
                                    </span>
                                }
                            </td>
                        </tr>
                        : null
                    }
                    { this.state.assign_to.value == 'category' && fpf_assign_to_values[this.state.assign_to.value]['is_available'] ?
                        <tr className="fpf-field fpf-field-categories">
                            <td className="fpf-label">
                                <label htmlFor="">{fpf_admin.categories_label}</label>
                            </td>

                            <td className="fpf-input">
                                {  fpf_assign_to_values[this.state.assign_to.value]['is_available'] ?
                                    <Select.Async
                                        name="categories"
                                        value={this.state.categories.value}
                                        onChange={this.categoriesChange}
                                        loadOptions={getCategoriesOptions}
                                        searchPromptText={fpf_admin.select_type_to_search}
                                        placeholder={fpf_admin.select_placeholder}
                                        //simpleValue={true}
                                        autoload={true}
                                        multi={true}
                                        ref="categories"
                                    />
                                    :
                                    <span>
                                        {fpf_admin.assign_to_adv} <a href={fpf_admin.assign_to_adv_link}>{fpf_admin.assign_to_adv_link_text}</a>
                                    </span>
                                }
                            </td>
                        </tr>
                        : null
                    }
                    <tr className="fpf-field fpf-field-menu-order-label">
                        <td className="fpf-label">
                            <label htmlFor="">{fpf_admin.menu_order_label}</label>
                        </td>
                        <td className="fpf-input">
                            { fpf_field_group_menu_order_is_available ?
                                <input type="number" className="fpf-field-menu-order" name="menu_order" value={this.state.menu_order.value} onChange={this.menuOrderChange} step="1" />
                                : <span>{fpf_admin.menu_order_adv} <a href={fpf_admin.menu_order_adv_link}>{fpf_admin.menu_order_adv_link_text}</a></span>
                            }
                        </td>
                    </tr>
                </tbody>
            </table>
        );
    }
}

class FPF_Settings_Container extends React.Component {

    constructor(props) {
        super(props);
        Settings = this;
        this.state = {
            settings: [ ]
        }
    }

    render() {
        return (
            <div className="fpf-fields-settings">
                <div className="fpf-inputs">
                    <FPF_Settings_Field
                        assign_to={fpf_settings.assign_to}
                        section={fpf_settings.section}
                        products={fpf_settings.products}
                        categories={fpf_settings.categories}
                        menu_order={fpf_settings.menu_order}
                        ref="settings_group"
                    />
                </div>
            </div>
        );
    }
}

class FPF_Actions extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            message: ""
        };
        this.onClick = this.onClick.bind(this);
    }

    onClick(val) {
        fpf_settings.post_title.value = document.getElementById( 'title' ).value;
        var xmlhttp = new XMLHttpRequest();
        var _this = this;
        xmlhttp.onreadystatechange = function() {
            if ( xmlhttp.readyState === 4 ) {
                var response = JSON.parse( xmlhttp.responseText );
                if ( xmlhttp.status === 200 ) {
                    if ( response.code === 'ok' ) {
                        _this.setState({
                            type: 'success',
                            message: response.message
                        });
                    }
                    else {
                        _this.setState({
                            type: 'error',
                            message: response.message
                        });
                    }
                }
                else {
                    _this.setState( { type: 'error', message: fpf_admin.save_error + xmlhttp.status } );
                }
            }
        };
        xmlhttp.open( 'POST', fpf_admin.rest_url + 'flexible_product_fields/v1/fields/' + fpf_settings.post_id.value, true );
        xmlhttp.setRequestHeader( 'Content-type', 'application/json' );
        xmlhttp.setRequestHeader( 'X-WP-Nonce', fpf_admin.rest_nonce );
        xmlhttp.send( JSON.stringify( fpf_settings ) );
    }

    render() {
        return (
            <div id="publishing-action">
                <span className="spinner"></span>
                <input name="save" type="button" className="button button-primary button-large" id="publish" value="Update" onClick={this.onClick} />
                <div className="fpf-update-status">{this.state.message}</div>
            </div>
        );
    }
}

jQuery(document).ready(function () {
    if ( typeof fpf_settings != 'undefined' ) {
        jQuery('form').keydown(function(e) {
            if(e.which == 13) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });

        ReactDOM.render(
            < FPF_Settings_Container />,
            document.getElementById('fpf_settings_container')
        );
        ReactDOM.render(
            < FPF_Fields_Container />,
            document.getElementById('fpf_fields_container')
        );

        var fpf_saved = false;

        jQuery('#publish').click(function (e) {
            if (fpf_saved) {
                return;
            }
            jQuery(this).focus();
            document.getElementById('publish').disabled = true;
            document.getElementById('publish').className += ' disabled';
            e.preventDefault();
            //jQuery(this).parent().find('.spinner').addClass('is-active');

            fpf_settings.post_title.value = document.getElementById('title').value;
            var xmlhttp = new XMLHttpRequest();
            var _this = this;
            xmlhttp.onreadystatechange = function () {
                document.getElementById('publish').disabled = false;
                document.getElementById('publish').classList.remove("disabled");
                if (xmlhttp.readyState === 4) {
                    var response = JSON.parse(xmlhttp.responseText);
                    if (xmlhttp.status === 200) {
                        if (response.code === 'ok') {
                            fpf_saved = true;
                            document.getElementById('publish').click();
                        }
                        else {
                            alert( response.message );
                        }
                    }
                    else {
                        alert( fpf_admin.save_error + xmlhttp.status );
                    }
                }
            };
            xmlhttp.open('POST', fpf_admin.rest_url + 'flexible_product_fields/v1/fields/' + fpf_settings.post_id.value, true);
            xmlhttp.setRequestHeader('Content-type', 'application/json');
            xmlhttp.setRequestHeader('x-wp-nonce', fpf_admin.rest_nonce);
            xmlhttp.send(JSON.stringify(fpf_settings));
        })

        jQuery('#save-post').click(function (e) {
            if (fpf_saved) {
                return;
            }
            e.preventDefault();

            fpf_settings.post_title.value = document.getElementById('title').value;
            var xmlhttp = new XMLHttpRequest();
            var _this = this;
            xmlhttp.onreadystatechange = function () {
                if (xmlhttp.readyState === 4) {
                    var response = JSON.parse(xmlhttp.responseText);
                    if (xmlhttp.status === 200) {
                        if (response.code === 'ok') {
                            fpf_saved = true;
                            document.getElementById('save').click();
                        }
                        else {
                            alert( response.message );
                        }
                    }
                    else {
                        alert( fpf_admin.save_error + xmlhttp.status );
                    }
                }
            };
            xmlhttp.open('POST', fpf_admin.rest_url + 'flexible_product_fields/v1/fields/' + fpf_settings.post_id.value, true);
            xmlhttp.setRequestHeader('Content-type', 'application/json');
            xmlhttp.setRequestHeader('x-wp-nonce', fpf_admin.rest_nonce);
            xmlhttp.send(JSON.stringify(fpf_settings));
        })

    }


});
