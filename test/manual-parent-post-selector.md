# Manual verification: hierarchical parent selection

These steps verify the AlphaListing block exposes the parent selector for hierarchical post types, persists the saved parent value, and stores the expected shortcode attributes.

## Prerequisites

* A WordPress site with the AlphaListing plugin activated.
* Capability to create and edit pages (or any hierarchical post type).

## Data setup

1. Create three published pages:
   * “Parent Listing Container”.
   * “Child A” assigned as a child of “Parent Listing Container”.
   * “Child B” assigned as a child of “Parent Listing Container”.
2. Note the numeric post ID for “Parent Listing Container”.

## Block regression check

1. Add a new page and insert the **AlphaListing** block.
2. In the block inspector, set:
   * **Display mode** → “Posts”.
   * **Post Type** → “Pages”.
3. ✅ Expected: The **Parent post** combobox appears underneath the Post Type selector.
4. Use the **Parent post** combobox to search for and select “Parent Listing Container”.
5. Publish or update the page, then refresh the editor.
6. ✅ Expected: After reload the **Parent post** combobox still shows “Parent Listing Container”.
7. Switch to the Code Editor view for the page.
8. ✅ Expected: The saved block markup contains `parent-post="<ID>` (where `<ID>` matches the post ID recorded for “Parent Listing Container”).
9. View the page on the front end.
10. ✅ Expected: The rendered shortcode includes the same `parent-post` attribute value.

## Regression guard (non-hierarchical post types)

1. Edit the block and change **Post Type** to a non-hierarchical type (e.g. “Posts”).
2. ✅ Expected: The **Parent post** combobox is hidden.
