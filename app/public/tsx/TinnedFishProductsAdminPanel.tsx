import { h, Component } from "preact";

let api_url: string = process.env.BRISTOLIAN_API_BASE_URL;

export interface TinnedFishProductsAdminPanelProps {
    initial_json_data: any;
}

interface Product {
    barcode: string;
    name: string;
    brand: string;
    species: string | null;
    weight: number | null;
    weight_drained: number | null;
    product_code: string | null;
    image_url: string | null;
    validation_status: string;
    created_at: string;
}

interface ValidationStatusOption {
    value: string;
    label: string;
}

interface TinnedFishProductsAdminPanelState {
    products: Product[];
    validation_statuses: ValidationStatusOption[];
    updating: Set<string>; // Set of barcodes currently being updated
    errors: Map<string, string>; // Map of barcode to error message
    refreshing: boolean; // Whether products are being refreshed
}

function getDefaultState(props: TinnedFishProductsAdminPanelProps): TinnedFishProductsAdminPanelState {
    const data = props.initial_json_data;
    
    return {
        products: data.products || [],
        validation_statuses: data.validation_statuses || [],
        updating: new Set(),
        errors: new Map(),
        refreshing: false,
    };
}

export class TinnedFishProductsAdminPanel extends Component<
    TinnedFishProductsAdminPanelProps,
    TinnedFishProductsAdminPanelState
> {
    constructor(props: TinnedFishProductsAdminPanelProps) {
        super(props);
        this.state = getDefaultState(props);
    }

    private handleUpdateValidationStatusResponse(response: Response, barcode: string): Promise<any> {
        console.log('Response status:', response.status, response.statusText);
        if (!response.ok) {
            return response.json().then((data: any) => {
                console.error('Error response:', data);
                throw new Error(data.error || `HTTP ${response.status}`);
            });
        }
        return response.json();
    }

    private handleUpdateValidationStatusSuccess(data: any, barcode: string): void {
        console.log('Success response:', data);
        // Update the product in the list
        const products = this.state.products.map((product) => {
            if (product.barcode === barcode) {
                return { ...product, validation_status: data.validation_status };
            }
            return product;
        });

        const updating = new Set(this.state.updating);
        updating.delete(barcode);

        this.setState({ products, updating });
    }

    private handleUpdateValidationStatusError(error: Error, barcode: string): void {
        console.error('Error updating validation status:', error);
        const errors = new Map(this.state.errors);
        errors.set(barcode, error.message);
        
        const updating = new Set(this.state.updating);
        updating.delete(barcode);

        this.setState({ errors, updating });
    }

    updateValidationStatus(barcode: string, validationStatus: string) {
        // Add to updating set
        const updating = new Set(this.state.updating);
        updating.add(barcode);
        
        // Clear any previous error for this barcode
        const errors = new Map(this.state.errors);
        errors.delete(barcode);
        
        this.setState({ updating, errors });

        const url = `${api_url}/api/tfd/v1/products/${encodeURIComponent(barcode)}/validation_status`;
        
        console.log('Updating validation status:', { barcode, validationStatus, url });
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                validation_status: validationStatus,
            }),
            credentials: 'include',
        })
            .then((response: Response) => this.handleUpdateValidationStatusResponse(response, barcode))
            .then((data: any) => this.handleUpdateValidationStatusSuccess(data, barcode))
            .catch((error: Error) => this.handleUpdateValidationStatusError(error, barcode));
    }

    handleStatusChange(barcode: string, event: Event) {
        const target = event.target as HTMLSelectElement;
        const newStatus = target.value;
        this.updateValidationStatus(barcode, newStatus);
    }

    private handleRefreshProductsResponse(response: Response): Promise<any> {
        if (!response.ok) {
            return response.json().then((data: any) => {
                throw new Error(data.error || `HTTP ${response.status}`);
            });
        }
        return response.json();
    }

    private handleRefreshProductsSuccess(data: any): void {
        if (data.success && data.products) {
            this.setState({ 
                products: data.products,
                refreshing: false 
            });
        } else {
            throw new Error('Invalid response format');
        }
    }

    private handleRefreshProductsError(error: Error): void {
        const errors = new Map(this.state.errors);
        errors.set('refresh', error.message);
        this.setState({ 
            errors,
            refreshing: false 
        });
    }

    refreshProducts() {
        // Clear any previous refresh error
        const errors = new Map(this.state.errors);
        errors.delete('refresh');
        
        this.setState({ refreshing: true, errors });

        const url = `${api_url}/api/tfd/v1/products`;
        
        fetch(url, {
            method: 'GET',
            // credentials: 'include',
        })
            .then((response: Response) => this.handleRefreshProductsResponse(response))
            .then((data: any) => this.handleRefreshProductsSuccess(data))
            .catch((error: Error) => this.handleRefreshProductsError(error));
    }

    private findValidationStatus(value: string): ValidationStatusOption | undefined {
        return this.state.validation_statuses.find(
            (status) => status.value === value
        );
    }

    private handleStatusSelectChange(barcode: string, event: Event): void {
        this.handleStatusChange(barcode, event);
    }

    private renderStatusOption(status: ValidationStatusOption) {
        return (
            <option key={status.value} value={status.value}>
                {status.label}
            </option>
        );
    }

    renderProductRow(product: Product, index: number) {
        const isUpdating = this.state.updating.has(product.barcode);
        const error = this.state.errors.get(product.barcode);
        const currentStatus = this.findValidationStatus(product.validation_status);

        return (
            <tr key={product.barcode}>
                <td>{product.barcode}</td>
                <td>{product.name}</td>
                <td>{product.brand}</td>
                <td>{product.species || ''}</td>
                <td>{product.weight !== null ? `${product.weight}g` : ''}</td>
                <td>{product.weight_drained !== null ? `${product.weight_drained}g` : ''}</td>
                <td>{product.product_code || ''}</td>
                <td>
                    <select
                        value={product.validation_status}
                        onChange={(e) => this.handleStatusSelectChange(product.barcode, e)}
                        disabled={isUpdating}
                    >
                        {this.state.validation_statuses.map((status) => this.renderStatusOption(status))}
                    </select>
                    {isUpdating && <span> Updating...</span>}
                    {error && <span style="color: red;"> Error: {error}</span>}
                </td>
                <td>{product.created_at}</td>
            </tr>
        );
    }

    private handleRefreshButtonClick(): void {
        this.refreshProducts();
    }

    private renderProductRowInTable(product: Product, index: number) {
        return this.renderProductRow(product, index);
    }

    render() {
        const refreshError = this.state.errors.get('refresh');

        return (
            <div class="tinned_fish_products_admin_panel">
                <div style="margin-bottom: 1em;">
                    <button 
                        onClick={() => this.handleRefreshButtonClick()}
                        disabled={this.state.refreshing}
                    >
                        {this.state.refreshing ? 'Refreshing...' : 'Refresh'}
                    </button>
                    {refreshError && (
                        <span style="color: red; margin-left: 1em;">
                            Error: {refreshError}
                        </span>
                    )}
                </div>
                {this.state.products.length === 0 ? (
                    <div>No products found.</div>
                ) : (
                    <table>
                        <thead>
                            <tr>
                                <th>Barcode</th>
                                <th>Name</th>
                                <th>Brand</th>
                                <th>Species</th>
                                <th>Weight</th>
                                <th>Weight Drained</th>
                                <th>Product Code</th>
                                <th>Validation Status</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            {this.state.products.map((product, index) => this.renderProductRowInTable(product, index))}
                        </tbody>
                    </table>
                )}
            </div>
        );
    }
}
