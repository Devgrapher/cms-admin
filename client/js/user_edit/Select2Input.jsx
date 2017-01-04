import React from 'react';

class Select2Input extends React.Component {
  componentWillUpdate(nextProps) {
    if (JSON.stringify(this.props.value) !== JSON.stringify(nextProps.value)) {
      $(this.selectInput).val(nextProps.value).trigger('change');
    }
  }

  componentDidMount() {
    const {onAdd, onRemove} = this.props;

    $(this.selectInput).select2();
    $(this.selectInput).on('select2:select', (e) => {
      const data = e.params.data;
      if (onAdd) {
        onAdd(data.id);
      }
    });

    $(this.selectInput).on('select2:unselect', (e) => {
      const data = e.params.data;
      if (onRemove) {
        onRemove(data.id);
      }
    });
  }

  renderOption(data) {
    return (
      <option key={data.id} value={data.id}>
        {data.text}
      </option>
    );
  }

  render() {
    const { value, name, placeholder, multiple, disabled } = this.props;

    return (
      <div>
        <select
          ref={(input) => { this.selectInput = input; }}
          name={name}
          data-placeholder={placeholder}
          value={value}
          multiple={multiple}
          style={{ width: '100%' }}
          disabled={disabled}
          onChange={() => {}}
        >
          {this.props.data.map(this.renderOption)}
        </select>
      </div>
    );
  }
}

Select2Input.propTypes = {
  name: React.PropTypes.string,
  value: React.PropTypes.array,
  data: React.PropTypes.array,
  multiple: React.PropTypes.bool,
  placeholder: React.PropTypes.string,
  disabled: React.PropTypes.bool,
  onAdd: React.PropTypes.func,
  onRemove: React.PropTypes.func,
};

Select2Input.defaultProps = {
  fetching: false,
  value: [],
  data: [],
  placeholder: '',
  multiple: true,
  onAdd: undefined,
  onRemove: undefined,
};

export default Select2Input;
