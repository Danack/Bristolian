import { h, Component } from "preact";

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

interface ApiTokenData {
    token: string;
    name: string;
    qr_code_url: string;
    created_at: string;
}

interface TinnedFishProductsAdminPanelState {
    products: Product[];
    validation_statuses: ValidationStatusOption[];
    updating: Set<string>; // Set of barcodes currently being updated
    errors: Map<string, string>; // Map of barcode to error message
    refreshing: boolean; // Whether products are being refreshed
    filterValidationStatus: string; // Selected validation status filter, empty string means "all"
    // API Token generation
    tokenName: string; // Input field for token name
    generatingToken: boolean; // Whether token is being generated
    generatedToken: ApiTokenData | null; // Generated token data
    tokenError: string | null; // Error message for token generation
}

function getDefaultState(props: TinnedFishProductsAdminPanelProps): TinnedFishProductsAdminPanelState {
    const data = props.initial_json_data;
    
    return {
        products: data.products || [],
        validation_statuses: data.validation_statuses || [],
        updating: new Set(),
        errors: new Map(),
        refreshing: false,
        filterValidationStatus: '', // Empty string means show all
        tokenName: '',
        generatingToken: false,
        generatedToken: null,
        tokenError: null,
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

        const url = `/api/tfd/v1/products/${encodeURIComponent(barcode)}/validation_status`;
        
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

        const url = `/api/tfd/v1/products`;
        
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

    private handleFilterChange(event: Event): void {
        const target = event.target as HTMLSelectElement;
        this.setState({ filterValidationStatus: target.value });
    }

    private getFilteredProducts(): Product[] {
        if (this.state.filterValidationStatus === '') {
            return this.state.products;
        }
        return this.state.products.filter(
            (product) => product.validation_status === this.state.filterValidationStatus
        );
    }

    private renderProductRowInTable(product: Product, index: number) {
        return this.renderProductRow(product, index);
    }

    private handleTokenNameChange(event: Event): void {
        const target = event.target as HTMLInputElement;
        this.setState({ tokenName: target.value, tokenError: null });
    }

    private handleGenerateTokenResponse(response: Response): Promise<any> {
        if (!response.ok) {
            return response.json().then((data: any) => {
                throw new Error(data.error || `HTTP ${response.status}`);
            });
        }
        return response.json();
    }

    private handleGenerateTokenSuccess(data: any): void {
        if (data.success && data.token && data.qr_code_url) {
            this.setState({
                generatingToken: false,
                generatedToken: {
                    token: data.token,
                    name: data.name,
                    qr_code_url: data.qr_code_url,
                    created_at: data.created_at,
                },
                tokenError: null,
                tokenName: '', // Clear the input after successful generation
            });
        } else {
            throw new Error('Invalid response format');
        }
    }

    private handleGenerateTokenError(error: Error): void {
        this.setState({
            generatingToken: false,
            tokenError: error.message,
        });
    }

    generateApiToken() {
        if (!this.state.tokenName.trim()) {
            this.setState({ tokenError: 'Token name is required' });
            return;
        }

        this.setState({ generatingToken: true, tokenError: null });

        const url = '/api/tfd/v1/admin/api-token/generate';
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                name: this.state.tokenName.trim(),
            }),
            credentials: 'include',
        })
            .then((response: Response) => this.handleGenerateTokenResponse(response))
            .then((data: any) => this.handleGenerateTokenSuccess(data))
            .catch((error: Error) => this.handleGenerateTokenError(error));
    }

    private handleGenerateTokenButtonClick(): void {
        this.generateApiToken();
    }

    render() {
        const refreshError = this.state.errors.get('refresh');
        const filteredProducts = this.getFilteredProducts();

        return (
            <div class="tinned_fish_products_admin_panel">
                <div style="margin-bottom: 2em; padding: 1em; border: 1px solid #ccc; border-radius: 4px;">
                    <h3 style="margin-top: 0;">API Token Generation</h3>
                    <p style="margin-bottom: 1em;">Generate an API token for the mobile app. The token will be displayed as a QR code that can be scanned.</p>
                    <div style="margin-bottom: 1em;">
                        <label style="display: block; margin-bottom: 0.5em;">
                            Token Name:
                        </label>
                        <input
                            type="text"
                            value={this.state.tokenName}
                            onChange={(e) => this.handleTokenNameChange(e)}
                            placeholder="e.g., John's iPhone, Test Device"
                            style="width: 300px; padding: 0.5em;"
                            disabled={this.state.generatingToken}
                        />
                    </div>
                    <button
                        onClick={() => this.handleGenerateTokenButtonClick()}
                        disabled={this.state.generatingToken || !this.state.tokenName.trim()}
                        style="margin-bottom: 1em;"
                    >
                        {this.state.generatingToken ? 'Generating...' : 'Generate API Token'}
                    </button>
                    {this.state.tokenError && (
                        <div style="color: red; margin-top: 0.5em;">
                            Error: {this.state.tokenError}
                        </div>
                    )}
                    {this.state.generatedToken && (
                        <div style="margin-top: 1.5em; padding: 1em; background-color: #f5f5f5; border-radius: 4px;">
                            <h4 style="margin-top: 0;">Token Generated Successfully</h4>
                            <p><strong>Name:</strong> {this.state.generatedToken.name}</p>
                            <p><strong>Created:</strong> {this.state.generatedToken.created_at}</p>
                            <div style="margin: 1em 0;">
                                <p><strong>QR Code:</strong></p>
                                <img 
                                    src={this.state.generatedToken.qr_code_url} 
                                    alt="API Token QR Code"
                                    style="border: 1px solid #ccc; padding: 0.5em; background-color: white;"
                                />
                            </div>
                            <div style="margin-top: 1em;">
                                <p><strong>Token (for manual entry):</strong></p>
                                <code style="display: block; padding: 0.5em; background-color: #f0f0f0; border-radius: 4px; word-break: break-all;">
                                    {this.state.generatedToken.token}
                                </code>
                            </div>
                            <p style="margin-top: 1em; font-size: 0.9em; color: #666;">
                                Scan the QR code with the mobile app to store the token. The token will be used to authenticate API requests.
                            </p>
                        </div>
                    )}
                </div>
                <div style="margin-bottom: 1em;">
                    <button 
                        onClick={() => this.handleRefreshButtonClick()}
                        disabled={this.state.refreshing}
                    >
                        {this.state.refreshing ? 'Refreshing...' : 'Refresh'}
                    </button>
                    <label style="margin-left: 1em; margin-right: 0.5em;">
                        Filter by Validation Status:
                    </label>
                    <select
                        value={this.state.filterValidationStatus}
                        onChange={(e) => this.handleFilterChange(e)}
                    >
                        <option value="">All</option>
                        {this.state.validation_statuses.map((status) => (
                            <option key={status.value} value={status.value}>
                                {status.label}
                            </option>
                        ))}
                    </select>
                    {refreshError && (
                        <span style="color: red; margin-left: 1em;">
                            Error: {refreshError}
                        </span>
                    )}
                </div>
                {filteredProducts.length === 0 ? (
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
                            {filteredProducts.map((product, index) => this.renderProductRowInTable(product, index))}
                        </tbody>
                    </table>
                )}
            </div>
        );
    }
}
