# Tinned Fish Diary API

API for looking up tinned fish product information by barcode.

## Feature Description

This feature provides a REST API for the Tinned Fish Diary mobile app to look up product information by barcode. It first checks the canonical database, then optionally fetches from external APIs (OpenFoodFacts) with proper copyright attribution.

## API Endpoint

- **Path**: `GET /api/tfd/v1/products/barcode/{barcode}`
- **Query Parameters**: `fetch_external` (boolean, optional, default: true)

## Important Files

### Database
- `db/migrations/29_tinned_fish_product.php` - Migration for the canonical product table

### Models
- `src/Bristolian/Model/TinnedFish/Product.php` - Product data model
- `src/Bristolian/Model/TinnedFish/Copyright.php` - Copyright attribution model
- `src/Bristolian/Model/TinnedFish/ProductError.php` - Error response model

### Repository
- `src/Bristolian/Repo/TinnedFishProductRepo/TinnedFishProductRepo.php` - Repository interface
- `src/Bristolian/Repo/TinnedFishProductRepo/PdoTinnedFishProductRepo.php` - PDO implementation

### Services
- `src/Bristolian/Service/TinnedFish/OpenFoodFactsFetcher.php` - External API fetcher
- `src/Bristolian/Service/TinnedFish/OpenFoodFactsApiException.php` - API exception
- `src/Bristolian/Service/TinnedFish/ProductNormalizer.php` - Data normalizer

### Parameters
- `src/Bristolian/Parameters/TinnedFish/BarcodeLookupParams.php` - Request parameters
- `src/Bristolian/Parameters/PropertyType/Barcode.php` - Barcode validation type
- `src/Bristolian/Parameters/PropertyType/OptionalBoolDefaultTrue.php` - Bool param type
- `src/Bristolian/Parameters/ProcessRule/StringToBoolDefaultTrue.php` - Bool process rule

### Responses
- `src/Bristolian/Response/TinnedFish/ProductLookupResponse.php` - Success response
- `src/Bristolian/Response/TinnedFish/ProductNotFoundResponse.php` - 404 response
- `src/Bristolian/Response/TinnedFish/InvalidBarcodeResponse.php` - 400 response
- `src/Bristolian/Response/TinnedFish/ExternalApiErrorResponse.php` - 502 response

### Controller
- `src/Bristolian/ApiController/TinnedFish.php` - Main controller

### Configuration
- `api/src/api_routes.php` - Route registration
- `api/src/api_injection_params.php` - Dependency injection config
