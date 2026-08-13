# Reseller Client API

Reference documentation for integrators connecting to the site as a **reseller client**.

| File | Format | Endpoint |
|------|--------|----------|
| [JsonApiReference.php](./JsonApiReference.php) | JSON | `{base_url}/api/index.php` |
| [XmlApiReference.php](./XmlApiReference.php) | XML | `{base_url}/gsmfusion_api` |

Each reference file documents one action per method with example requests and example responses in comments. These files are **documentation only** and are not executed by the application.

---

## Which API should I use?

| Need | Use |
|------|-----|
| JSON automation (most common) | **JSON API** |
| XML integrations | **XML API** |
| **File orders** (upload `.log`, `.txt`, etc.) | **XML only** — `placefileorder`, `getfileorder` |
| Server / IMEI / remote orders | Either API |
| Single call for all service types (JSON) | JSON `imeiservicelist` (see API Format below) |

**File orders are XML-only.** The JSON API has no `placefileorder` or `getfileorder` actions. Use the XML reference for file workflows.

---

## Authentication (both APIs)

Every request must identify a **client user** with API access enabled.

### JSON API

| Parameter | Description |
|-----------|-------------|
| `username` | Client username |
| `apiaccesskey` | API key from admin → User → API |
| `action` | Action name |
| `requestformat` | `JSON` (recommended) |

### XML API

| Parameter | Description |
|-----------|-------------|
| `userId` | Client username |
| `apiKey` | API key |
| `action` | Action name |

### Prerequisites (enforced server-side)

1. User account is **active** (not disabled by the administrator)
2. **API access is enabled** for that user
3. Valid **`apiaccesskey` / `apiKey`**
4. If **IP restriction** is enabled for the site, the caller’s IP must be allowed (a first request from a new IP may be registered automatically when no allow list exists yet)
5. Site is **not** in maintenance mode

Failed auth returns JSON `ERROR` or XML `<error>` — see each reference file for the full list.

---

## JSON only: API Format

Configured **per user** by the site administrator under User → API → **API Format**:

| Setting | Effect on `imeiservicelist` |
|---------|----------------------------|
| **Old Format** (default) | Returns **multiple** `SUCCESS` blocks (IMEI, Server/File, Remote) |
| **New Format** | Returns **one** `SUCCESS` block with `"MESSAGE": "All Services"` |

- Not sent as a request parameter — applied automatically for the authenticated user
- **Only affects `imeiservicelist`**; all other actions are unchanged

Details and example payloads: [JsonApiReference.php → getServiceList()](./JsonApiReference.php).

---

## Optional: client order ID (`orderId`) — deduplication

Both APIs accept an optional top-level **`orderId`** when placing orders. This is **your** own reference id, used to avoid duplicate submissions if you retry the same client order.

### JSON API

Send as POST/GET field (not inside `parameters` XML):

```
orderId=12345
```

Supported on: `placeimeiorder`, `placeserverorder`, `placeremoteorder`.

If an order with the same `orderId` + service + user already exists, the API returns the existing reference instead of creating a duplicate.

### XML API

Same field name: `orderId`. Supported on: `placeorder`, `placeserverorder`, `placeremoteorder`, `placefileorder`.

---

## Public endpoints

### Authenticated reseller endpoints

| Endpoint | Auth |
|----------|------|
| `POST /api/index.php` | `username` + `apiaccesskey` |
| `POST /gsmfusion_api` | `userId` + `apiKey` |

### Read-only metadata

| Endpoint | Notes |
|----------|--------|
| `GET /api/get_version` | Returns app version metadata only (no user data) |

---

## Quick action index

### JSON API

`accountinfo` · `imeiservicelist` · `meplist` · `placeimeiorder` · `placeserverorder` · `placeremoteorder` · `getimeiorder` · `getremoteorder`

### XML API

`accountinfo` · `imeiservices` · `fileservices` · `serverservices` · `remoteservices` · `getservices` · `getservicedtls` · `placeorder` · `placefileorder` · `placeserverorder` · `placeremoteorder` · `getimeis` · `getfileorder` · `getserverorder` · `getremoteorder` · `getproviders` · `getmobiles`

---

## Implementation notes for client authors

- JSON order actions use a **`parameters`** XML string; custom fields go in `<CUSTOMFIELD>` as **base64-encoded JSON**
- XML order actions pass custom fields as **top-level POST fields** (field label; spaces as underscores)
- JSON account info response uses the key **`AccoutInfo`** (spelling as returned by the API)
- Order reference: JSON returns **`REFERENCEID`**; XML returns **`<id>`** — use either value when checking order status
- Always use **HTTPS** in production
