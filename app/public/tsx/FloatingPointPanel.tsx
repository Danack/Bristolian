import {h, Component} from "preact";

export interface FloatingPointPanelProps {
     // no properties currently
}

interface FloatingPointPanelState {
    input_value: string
    sign: number;
    exponent: Array<number>;
    fraction: Array<number>;
    modified: boolean;
}


function getBit(i:number, value:number)
{
    const buffer = new ArrayBuffer(64);

    const view = new DataView(buffer);
    view.setFloat64(0, value);

    let byte_position = Math.floor(i / 8);
    let bit_position = (i - (byte_position * 8));
    let bit_mask = 1 << bit_position;

    let byte_value = view.getUint8(7 - byte_position);

    return (byte_value & bit_mask) >> bit_position;
}

function removeTrailingZeroes(exact: string)
{
    // Not a float, bail out
    if (exact.indexOf(".") == -1) {
        return exact;
    }

    do {
        let last_char = exact.charAt(exact.length - 1);

        // Number has no decimal values, chop off decimal point
        if (last_char == '.') {
            return exact.substring(0, exact.length - 1);
        }
        // Number has some decimal values
        if (last_char != '0') {
            return exact;
        }
        exact = exact.substring(0, exact.length - 1);
    } while (exact.length >= 1);
}

function getState(string_value: string): FloatingPointPanelState
{
    let value = eval(string_value);
    let parsed_value = parseFloat(value);

    if (parsed_value == Infinity) {
        return {
            input_value: string_value,
            sign: 0,
            exponent: Array(11).fill(1),
            fraction: Array(52).fill(0),
            modified: false
        }
    }
    if (parsed_value == -Infinity) {
        return {
            input_value: string_value,
            sign: 1,
            exponent: Array(11).fill(1),
            fraction: Array(52).fill(0),
            modified: false
        }
    }

    let sign = getBit(63, parsed_value)
    let exponent = [];
    for (let i = 0; i < 11; i += 1) {
        let bit = getBit(62 - i, parsed_value)
        exponent.push(bit);
    }

    let fraction = [];
    for (let i = 0; i < 52; i += 1) {
        let bit = getBit(51 - i, parsed_value)
        fraction.push(bit);
    }

    return {
        input_value: string_value,
        sign: sign,
        exponent: exponent,
        fraction: fraction,
        modified: false
    }
}


function getDefaultState(/*initialControlParams: object*/): FloatingPointPanelState
{
    return getState("0.05");
}


export class FloatingPointPanel extends Component<FloatingPointPanelProps, FloatingPointPanelState> {

    constructor(props: FloatingPointPanelProps) {
      super(props);
      this.state = getDefaultState(/*props.initialControlParams*/);
    }

    componentDidMount() {
        // this.restoreStateFn = (event:any) => this.restoreState(event.state);
        // // @ts-ignore: I don't understand that error message.
        // window.addEventListener('popstate', this.restoreStateFn);
    }

    componentWillUnmount() {
        // // unbind the listener
        // // @ts-ignore: I don't understand that error message.
        // window.removeEventListener('popstate', this.restoreStateFn, false);
        // this.restoreStateFn = null;
    }

    restoreState(state_to_restore: object) {
        // if (state_to_restore === null) {
        //     this.setState(getDefaultState(this.props.initialControlParams));
        //     return;
        // }
        //
        // this.setState(state_to_restore);
        // this.triggerSetImageParams();
    }

    handleChange(e:Event /*HTMLInputElement*/ /*: React.ChangeEvent<HTMLInputElement>*/)
    {
        // @ts-ignore:why is HTMLInputElement not recognised?
        this.setState(getState(e.target.value));
    }


    getOffsetRow() {
        let entries = [];
        for (let i = 0; i < 64; i += 1) {
            let colour_class = "floating_point_bit";
            if ((((63 - i) >> 2) % 2) != 0) {
                colour_class = "floating_point_bit floating_point_bit_dark";
            }

            let item = <td class={colour_class}>{63 - i}</td>;

            entries.push(item)
        }
        return entries;
    }

    toggleSign() {
      let new_value = 1;
      if (this.state.sign == 1) {
          new_value = 0;
      }
      this.setState({
          sign: new_value,
          modified: true
      });
    }

    toggleExponent(i: number) {
        let new_value = 1;
        if (this.state.exponent[i] == 1) {
            new_value = 0;
        }
        let new_exponent_values = this.state.exponent;
        new_exponent_values[i] = new_value

        this.setState({
            exponent: new_exponent_values,
            modified: true
        });
    }

    getSignCells() {
        let number = 0;
        if (this.state.sign) {
            number = 1;
        }

        return <td
            class='floating_point_sign_number'
            onClick={() => this.toggleSign()}
        >{number}</td>;
    }

    getExponentCell(i:number) {
        let number = this.state.exponent[i];
        return <td
            class='floating_point_exponent_number'
            onClick={() => this.toggleExponent(i)}
        >{number}</td>;
    }

    getExponentCells() {
        let exponent_cells = [];
        for (let i = 0 ; i < 11; i += 1) {
            exponent_cells.push(this.getExponentCell(i));
        }

        return exponent_cells;
    }

    toggleFraction(i: number) {
        let new_value = 1;
        if (this.state.fraction[i] == 1) {
            new_value = 0;
        }
        let new_fraction_values = this.state.fraction;
        new_fraction_values[i] = new_value

        this.setState({
            fraction: new_fraction_values,
            modified: true
        });
    }

    fractionCell(i:number) {
        let number = this.state.fraction[i];
        return <td
            class='floating_point_fraction_number'
            onClick={() => this.toggleFraction(i)}
        >{number}</td>;
    }

    getFractionCells() {
        let fraction_cells = [];
        for (let i = 0 ; i < 52; i += 1) {
            fraction_cells.push(this.fractionCell(i));
        }

        return fraction_cells;
    }


    getFractionValue() {
        let value = 1.0;
        for (let i = 0 ; i < 52; i += 1) {
            if (this.state.fraction[i]) {
                value += Math.pow(2, -(i+1));
                if (isNaN(value)) {
                    debugger;
                }
            }
        }

        return value;
    }

    getExponentValue() {
        let value = 0;
        for (let i = 0 ; i < 11; i += 1) {
            value = value << 1;
            value = value + this.state.exponent[i]
        }
        return value - 1023;
    }

    getHexString() {
        let hex_string = "0x";

        let number_row:Array<number> = [this.state.sign];
        number_row.push(...this.state.exponent);
        number_row.push(...this.state.fraction);

        for (let i = 0; i<64; i += 4) {
            let nibble = (number_row[i + 0] << 3) +
                         (number_row[i + 1] << 2) +
                         (number_row[i + 2] << 1) +
                         (number_row[i + 3] << 0);

            hex_string += nibble.toString(16).toUpperCase();
        }

        return hex_string;
    }

    getExactValue() {
        const buffer = new ArrayBuffer(64);
        const view = new DataView(buffer);

        let bits:Array<number> = [this.state.sign];
        bits.push(...this.state.exponent);
        bits.push(...this.state.fraction);

        for (let i = 0; i<64; i += 8) {
            let byte_value =
                (bits[i + 0] << 7) +
                (bits[i + 1] << 6) +
                (bits[i + 2] << 5) +
                (bits[i + 3] << 4) +
                (bits[i + 4] << 3) +
                (bits[i + 5] << 2) +
                (bits[i + 6] << 1) +
                (bits[i + 7] << 0);

            view.setUint8(Math.floor(i / 8), byte_value);
        }

        let value = view.getFloat64(0);

        let exact = value.toPrecision(64);


        // return exact;
        return removeTrailingZeroes(exact);
    }

    incrementExponent() {
        let new_exponent = this.state.exponent;
        let carry = 1;
        for (let i=10; i >= 0; i -= 1) {
            let value = new_exponent[i] + carry;
            new_exponent[i] = value % 2;
            carry = value >> 1;
        }
        this.setState({
            exponent: new_exponent,
            modified: true
        })
    }

    decrementExponent() {
        let new_exponent = this.state.exponent;
        let carry = 1;
        for (let i=10; i >= 0; i -= 1) {
            let value = new_exponent[i] - carry;
            if (value == -1) {
                new_exponent[i] = 1;
                carry = 1;
            }
            else {
                new_exponent[i] = value;
                carry = 0;
            }
        }
        this.setState({exponent: new_exponent})
    }

    leftShiftExponent() {
        let new_exponent = this.state.exponent.slice(1);
        new_exponent.push(0);
        this.setState({
            exponent: new_exponent,
            modified: true
        });
    }

    rightShiftExponent() {
        let new_exponent = this.state.exponent.slice(0, -1);
        new_exponent.unshift(0);
        this.setState({
            exponent: new_exponent,
            modified: true
        });
    }

    getExponentButtons() {
        return <span>
            <span class="floating_point_control" onClick={() => this.incrementExponent()}>+1</span>
            <span class="floating_point_control" onClick={() => this.decrementExponent()}>-1</span>
            <span class="floating_point_control" onClick={() => this.leftShiftExponent()}>&lt;&lt;</span>
            <span class="floating_point_control" onClick={() => this.rightShiftExponent()}>&gt;&gt;</span>
        </span>
    }

    getFractionButtons() {
        return <span>
            <span class="floating_point_control" onClick={() => this.incrementFraction()}>+1</span>
            <span class="floating_point_control" onClick={() => this.decrementFraction()}>-1</span>
            <span class="floating_point_control" onClick={() => this.leftShiftFraction()}>&lt;&lt;</span>
            <span class="floating_point_control" onClick={() => this.rightShiftFraction()}>&gt;&gt;</span>
        </span>
    }

    incrementFraction() {
        let new_fraction = this.state.fraction;
        let carry = 1;
        for (let i=51; i >= 0; i -= 1) {
            let value = new_fraction[i] + carry;
            new_fraction[i] = value % 2;
            carry = value >> 1;
        }
        this.setState({
            fraction: new_fraction,
            modified: true
        })
    }

    decrementFraction() {
        let new_fraction = this.state.fraction;
        let carry = 1;
        for (let i=51; i >= 0; i -= 1) {
            let value = new_fraction[i] - carry;
            if (value == -1) {
                new_fraction[i] = 1;
                carry = 1;
            }
            else {
                new_fraction[i] = value;
                carry = 0;
            }
        }
        this.setState({
            fraction: new_fraction,
            modified: true
        })
    }

    leftShiftFraction() {
        let new_fraction = this.state.fraction.slice(1);
        new_fraction.push(0);
        this.setState({
            fraction: new_fraction,
            modified: true
        });
    }

    rightShiftFraction() {
        let new_fraction = this.state.fraction.slice(0, -1);
        new_fraction.unshift(0);
        this.setState({
            fraction: new_fraction,
            modified: true
        });
    }

    resetValue() {
        this.setState(getState(this.state.input_value));
    }

    render(props: FloatingPointPanelProps, state: FloatingPointPanelState) {
        let bits_row = this.getOffsetRow();
        let number_row = [this.getSignCells()];

        number_row.push(...this.getExponentCells())
        number_row.push(...this.getFractionCells())

        let sign = this.state.sign ? "-1" : "1";
        let fraction = this.getFractionValue();
        let exponent = this.getExponentValue();
        let hex_string = this.getHexString();
        let exact_value = this.getExactValue();

        let exponent_buttons = this.getExponentButtons();
        let fraction_buttons = this.getFractionButtons();
        let reset_button = <span></span>;

        if (this.state.modified) {
            reset_button = <span
                class="floating_point_control"
                onClick={() => this.resetValue()}
            >Reset</span>
        }

        return  <div class='floating_point_panel_react'>
            <h3>Input</h3>
            <div>
                <input type='text'
                       width="128"
                       style="width: 300px;"
                       onChange={(e) => this.handleChange(e)}
                       value={this.state.input_value}></input>
                {reset_button}
            </div>

            <h3>64 bit representation</h3>
            <table>
                <tr>
                    {bits_row}
                </tr>
                <tr>
                    {number_row}
                </tr>
                <tr>
                    <td colSpan={1}></td>
                    <td colSpan={11}>{exponent_buttons}</td>
                    <td colSpan={52}>{fraction_buttons}</td>
                </tr>
            </table>


            <h3>Something</h3>
            <table>
                <tr>
                    <td>Sign</td>
                    <td></td>
                    <td>Exponent</td>
                    <td></td>
                    <td>Fraction</td>
                    <td></td>
                    <td>Exact value</td>
                </tr>
                <tr>
                    <td class="floating_point_sign_number">{sign}</td>
                    <td>x</td>
                    <td class="floating_point_exponent_number">
                        2^<span class="floating_point_exponent_number">{exponent}</span>
                    </td>
                    <td>x</td>
                    <td class="floating_point_fraction_number">{fraction}</td>
                    <td>=</td>
                    <td>{exact_value}</td>
                </tr>

            </table>

            <h3>Hex representation</h3>
            <div>{hex_string}</div>

            <h3>Some interesting numbers</h3>
            <table>
                <tr>
                    <td>First non-representable int</td>
                    <td>9007199254740993</td>
                </tr>
            </table>
        </div>;
    }
}


// http://evanw.github.io/float-toy/
// https://floating-point-gui.de/formats/fp/

// https://0.30000000000000004.com/
// https://docs.oracle.com/cd/E19957-01/806-3568/ncg_goldberg.html
// https://www.doc.ic.ac.uk/~eedwards/compsys/float/
// https://www.h-schmidt.net/FloatConverter/IEEE754.html






