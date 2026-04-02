---
ticket: SCON-204
url: https://stellarwp.atlassian.net/browse/SCON-204
status: todo
---

# Wire portal into the React app

## Problem

Components import `PRODUCTS` from `@/data/products` — a static array baked into the JS bundle. The portal needs to come from the server.

## Proposed solution

Add a React Query hook that fetches from the portal endpoint. Components that currently import the static `PRODUCTS` array switch to consuming the hook's data.
