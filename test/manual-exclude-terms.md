# Manual verification: `[alphalisting exclude-terms]`

These steps exercise the shortcode, block, and widget flows to ensure the `exclude-terms`
attribute removes posts (and term listings) assigned to the referenced taxonomy term IDs.

## Prerequisites

* A WordPress site with the AlphaListing plugin activated.
* Access to create categories, posts, widgets, and pages.

## Data setup

1. Create two categories (or any hierarchical taxonomy terms):
   * `Manual Keep` – note its numeric term ID (e.g. `201`).
   * `Manual Drop` – note its numeric term ID (e.g. `202`).
2. Create three posts:
   * “Visible Post A” assigned only to `Manual Keep`.
   * “Hidden Post B” assigned to both `Manual Keep` and `Manual Drop`.
   * “Visible Post C” assigned only to `Manual Keep`.
3. Confirm the three posts are published.

## Shortcode regression check

1. Create a new page containing:
   ```
   [alphalisting display="posts" taxonomy="category" exclude-terms="202"]
   ```
   (Replace `202` with the recorded term ID for `Manual Drop`.)
2. View the page on the front end.
3. ✅ Expected: “Hidden Post B” is missing from the listing, while “Visible Post A” and
   “Visible Post C” remain visible.

## Term listing regression check

1. Create a new page containing:
   ```
   [alphalisting display="terms" taxonomy="category" exclude-terms="202"]
   ```
   (Replace `202` with the recorded term ID for `Manual Drop`.)
2. View the page on the front end.
3. ✅ Expected: The category picker omits the `Manual Drop` term while still listing
   `Manual Keep` and `Manual Keep Too`.

## Block UI regression check

1. Add a new page and insert the **AlphaListing** block.
2. In the block inspector, set:
   * **Display mode** → “Posts”.
   * **Taxonomy** → “Categories”.
   * **Taxonomy terms** → `Manual Keep`.
   * **Terms to exclude (IDs)** → enter the numeric ID recorded for `Manual Drop`.
3. Update/publish the page and view it on the front end.
4. ✅ Expected: “Hidden Post B” does not appear. The block’s saved markup shows the
   shortcode attributes storing the numeric term ID.

## Widget UI regression check

1. Open *Appearance → Widgets*.
2. Add the **AlphaListing** widget to any sidebar.
3. Configure the widget for **Display** “Posts”, set **Taxonomy** “Categories”, and enter the
   numeric ID for `Manual Drop` in **Terms to exclude (IDs)**.
4. Save the widget and view a page that renders the sidebar.
5. ✅ Expected: “Hidden Post B” is excluded from the listing while posts without the
   excluded term remain visible.
