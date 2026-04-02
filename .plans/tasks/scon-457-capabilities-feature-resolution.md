---
ticket: SCON-457
url: https://stellarwp.atlassian.net/browse/SCON-457
status: todo
---

# Resolve feature availability from capabilities

## Problem

Feature availability (`is_available`) is currently determined by comparing the site's licensed tier rank against each feature's minimum tier rank from the portal. This means availability is strictly a function of what tier the portal says you should have.

The V4 products endpoint now returns a `capabilities` array on each product entry. These capabilities map directly to feature slugs and represent what the license actually grants. This is the source of truth for availability, not the portal tier. Capabilities handle cases the portal alone cannot, like grandfathered access after a tier restructure, one-time promotional grants, or individual exceptions made for a specific license.

## Proposed solution

Change `Resolve_Feature_Collection` to determine `is_available` by checking whether the feature's slug appears in the product entry's capabilities array, instead of comparing tier ranks. The portal still defines which features exist, their metadata, and which tier they belong to for display purposes. But the capabilities array is what decides access.

## Requirements

- A feature is available if its slug appears in the product entry's capabilities array.
- A feature that is capable but outside the user's portal tier should be resolved as available.
- A feature that is in the user's portal tier but not in their capabilities should be resolved as unavailable.
- Populate the fixture licensing JSON with the feature slugs each tier would have granted through the portal, so existing tests pass with the new resolution logic.
