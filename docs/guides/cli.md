# WP-CLI Commands

Harbor registers WP-CLI commands automatically when WP-CLI is present. No additional setup is needed.

## Command Reference

### `wp harbor license`

Manage the unified license key.

| Command    | Usage                                | Description                                               |
| ---------- | ------------------------------------ | --------------------------------------------------------- |
| `get`      | `wp harbor license get`              | Show the current license key and associated products      |
| `set`      | `wp harbor license set <key>`        | Validate and store a license key                          |
| `lookup`   | `wp harbor license lookup <key>`     | Look up products for a key without storing it             |
| `validate` | `wp harbor license validate <slug>`  | Validate a product on this domain (may consume a seat)    |
| `delete`   | `wp harbor license delete`           | Delete the stored unified license key                     |
| `legacy`   | `wp harbor license legacy`           | List legacy per-plugin licenses from all Harbor instances |

### `wp harbor portal`

Manage the product portal.

| Command    | Usage                                  | Description                                       |
| ---------- | -------------------------------------- | ------------------------------------------------- |
| `list`     | `wp harbor portal list`               | List all products in the portal                  |
| `tiers`    | `wp harbor portal tiers <slug>`       | Show tiers for a specific product                 |
| `features` | `wp harbor portal features <slug>`    | Show features for a specific product              |
| `refresh`  | `wp harbor portal refresh`            | Force refresh the portal from the API            |
| `status`   | `wp harbor portal status`             | Show when the portal was last fetched and errors |
| `delete`   | `wp harbor portal delete`             | Delete the cached portal                         |

### `wp harbor feature`

Manage Harbor features.

| Command      | Usage                                    | Description                                       |
| ------------ | ---------------------------------------- | ------------------------------------------------- |
| `list`       | `wp harbor feature list`                 | List features with optional filters               |
| `get`        | `wp harbor feature get <slug>`           | Show detailed information for a single feature    |
| `is-enabled` | `wp harbor feature is-enabled <slug>`    | Check if a feature is enabled (exit code 0 = yes) |
| `enable`     | `wp harbor feature enable <slug>`        | Enable a feature                                  |
| `disable`    | `wp harbor feature disable <slug>`       | Disable a feature                                 |
| `update`     | `wp harbor feature update <slug>`        | Update a feature to the latest version            |

## License Commands

### get

Shows the current license key and associated products.

```bash
wp harbor license get [--fields=<fields>] [--format=<format>]
```

**Default fields:** `product_slug, tier, status, expires, site_limit, active_count`

**Available fields:** `product_slug`, `tier`, `pending_tier`, `status`, `expires`, `site_limit`, `active_count`, `over_limit`, `installed_here`, `validation_status`, `is_valid`

**Examples:**

```bash
wp harbor license get
wp harbor license get --format=json
```

### set

Validates and stores a license key. Does not activate any product or consume a seat.

```bash
wp harbor license set <key> [--network] [--fields=<fields>] [--format=<format>]
```

| Option      | Description                               |
| ----------- | ----------------------------------------- |
| `<key>`     | The license key (must start with `LWSW-`) |
| `--network` | Store at the network level (multisite)    |

**Examples:**

```bash
wp harbor license set LWSW-abcdef-123456
wp harbor license set LWSW-abcdef-123456 --network
```

### lookup

Looks up products for a key without storing it.

```bash
wp harbor license lookup <key> [--fields=<fields>] [--format=<format>]
```

**Examples:**

```bash
wp harbor license lookup LWSW-abcdef-123456
```

### validate

Validates a product on this domain using the stored license key. This may consume an activation seat.

```bash
wp harbor license validate <product_slug>
```

**Examples:**

```bash
wp harbor license validate kadence
```

### delete

Deletes the stored unified license key. Does not free any activation seats on the licensing service.

```bash
wp harbor license delete [--network]
```

| Option      | Description                               |
| ----------- | ----------------------------------------- |
| `--network` | Delete from the network level (multisite) |

**Examples:**

```bash
wp harbor license delete
wp harbor license delete --network
```

### legacy

Lists legacy per-plugin licenses discovered across all Harbor instances. Read-only view of old-style keys stored individually by each plugin before unified licensing.

```bash
wp harbor license legacy [--fields=<fields>] [--format=<format>]
```

**Default fields:** `slug, name, product, key, status, expires_at`

**Available fields:** `slug`, `name`, `product`, `key`, `status`, `page_url`, `expires_at`

**Examples:**

```bash
wp harbor license legacy
wp harbor license legacy --format=json
```

## Portal Commands

### list

Lists all products in the portal.

```bash
wp harbor portal list [--format=<format>]
```

**Default fields:** `product_slug, tiers, features`

**Examples:**

```bash
wp harbor portal list
wp harbor portal list --format=json
```

### tiers

Shows tiers for a specific product.

```bash
wp harbor portal tiers <product_slug> [--fields=<fields>] [--format=<format>]
```

**Default fields:** `slug, name, rank, price, currency, purchase_url`

**Available fields:** `slug`, `name`, `rank`, `price`, `currency`, `features`, `herald_slugs`, `purchase_url`

**Examples:**

```bash
wp harbor portal tiers kadence
wp harbor portal tiers kadence --format=json
```

### features

Shows features for a specific product.

```bash
wp harbor portal features <product_slug> [--fields=<fields>] [--format=<format>]
```

**Default fields:** `slug, kind, minimum_tier, name, category`

**Available fields:** `slug`, `kind`, `minimum_tier`, `name`, `description`, `category`, `plugin_file`, `wporg_slug`, `download_url`, `version`, `authors`, `documentation_url`

**Examples:**

```bash
wp harbor portal features kadence
wp harbor portal features kadence --format=json
```

### refresh

Force refreshes the portal from the API, then displays the resulting product list.

```bash
wp harbor portal refresh [--format=<format>]
```

**Examples:**

```bash
wp harbor portal refresh
```

### status

Shows when the portal was last fetched and any errors.

```bash
wp harbor portal status
```

**Examples:**

```bash
wp harbor portal status
```

### delete

Deletes the cached portal. The next request for the portal will fetch fresh data from the API.

```bash
wp harbor portal delete
```

**Examples:**

```bash
wp harbor portal delete
```

## Feature Commands

### list

Lists features with optional filters.

```bash
wp harbor feature list [--product=<product>] [--tier=<tier>] [--available=<bool>] [--type=<type>] [--fields=<fields>] [--format=<format>]
```

**Options:**

| Option                | Description                                                      |
| --------------------- | ---------------------------------------------------------------- |
| `--product=<product>` | Filter by product (e.g. `kadence`)                               |
| `--tier=<tier>`       | Filter by tier (e.g. `Tier 1`)                                   |
| `--available=<bool>`  | Filter by availability (`true` or `false`)                       |
| `--type=<type>`       | Filter by type (`plugin`, `theme`)                               |
| `--fields=<fields>`   | Comma-separated field list                                       |
| `--format=<format>`   | Output format: `table` (default), `json`, `csv`, `yaml`, `count` |

**Default fields:** `slug, name, type, product, is_available, is_enabled`

**Available fields:**

- All types: `slug`, `name`, `description`, `type`, `product`, `tier`, `is_available`, `is_enabled`, `documentation_url`
- Plugin and Theme: `installed_version`, `release_date`, `wporg_slug`
- Plugin only: `plugin_file`

**Examples:**

```bash
# Table output (default)
wp harbor feature list

# JSON for scripting
wp harbor feature list --format=json

# Count features in a product
wp harbor feature list --product=kadence --format=count

# Show plugin-specific fields
wp harbor feature list --type=plugin --fields=slug,plugin_file,wporg_slug
```

### get

Shows detailed information for a single feature.

```bash
wp harbor feature get <slug> [--fields=<fields>] [--format=<format>]
```

**Examples:**

```bash
wp harbor feature get my-feature
wp harbor feature get my-feature --format=json
```

### is-enabled

Checks whether a feature is currently enabled. Exits with code 0 if enabled, 1 if not.

```bash
wp harbor feature is-enabled <slug>
```

**Examples:**

```bash
# Check in a script
if wp harbor feature is-enabled my-feature; then
  echo "Feature is enabled"
fi
```

### enable

Enables a feature.

```bash
wp harbor feature enable <slug>
```

**Examples:**

```bash
wp harbor feature enable my-feature
```

### disable

Disables a feature.

```bash
wp harbor feature disable <slug>
```

**Examples:**

```bash
wp harbor feature disable my-feature
```

### update

Updates a feature to the latest available version.

```bash
wp harbor feature update <slug>
```

**Examples:**

```bash
wp harbor feature update my-feature
```

## Scripting Patterns

### JSON piping

```bash
# Get all feature slugs
wp harbor feature list --format=json | jq -r '.[].slug'

# Get enabled features
wp harbor feature list --format=json | jq '[.[] | select(.is_enabled == "true")]'

# Get legacy license keys
wp harbor license legacy --format=json | jq -r '.[].key'
```

### Conditional logic

```bash
if wp harbor feature is-enabled my-feature; then
  echo "my-feature is enabled"
else
  wp harbor feature enable my-feature
fi
```

### Batch operations

```bash
# Enable all available plugin features
for slug in $(wp harbor feature list --type=plugin --available=true --format=json | jq -r '.[].slug'); do
  wp harbor feature enable "$slug"
done
```

## Cross-Instance Safety

When multiple vendor-prefixed copies of Harbor are active, only the highest version registers CLI commands. This uses the same `Version::should_handle()` mechanism as the REST API routes.
