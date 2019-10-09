INTRODUCTION
------------

This module provides dragging entities and saving their order.


REQUIREMENTS
-------------

Views, Views UI, Filter, User, System module enabled.


INSTALLATION
------------

Enable this module from extend list page /admin/modules.


CONFIGURATION
-------------

1) Activate Draggableviews module at /admin/modules.
2) Create a new view
    - Goto '/admin/structure/views/add' on your site.
    - Check off 'Create a page'.
    - Check off 'Create a block'.
    - Set the 'Display format' for the page to what you desire.
    - Set the "'Display format' of" to fields.
    - Set the 'Display format' for the block to table.
    - Fill in the rest of the views information.
    - Click Save & edit button.
3) Under the "FIELDS" section, do you see "Content: Title"?  If you do not:
    - Click 'add' button at the "Fields" section and choose field
    "Content:title", add and apply.
4) Add the Draggableviews Field:
    - Click Add button at the "FIELDS" section.
    - At the top of the overlay, Change "For: 'All displays'" to 'This block
     (override)'.
      - If you do not do this then the field will be add to all displays and
      will prevent your page display from using the block display to sort the
      order.
5) Click Add button at the "SORT CRITERIA" section choose field
"Draggableviews: Weight", add and choose sort asc, then apply.
6) Under the "SORT CRITERIA" section, do you see "Content: Post date (asc)"?
 If you do:
    - Click on it.  At the bottom, click the 'Remove' button.
      - An alternative is to rearrange the "SORT CRITERIA" order, making sure
      'Draggableviews: Weight (asc) appears first (or on top).
7) Save the view and you're done.
*Things to confirm after you saved your new view.
- In the Administrative Views UI, Go back to your View's 'page' display.
  -> Click 'Draggableviews: Weight (asc)' under 'SORT CRITERIA'
  -> You should see:
  Display sort as:
  <title of view> (<display title>)

  This should the view and block display you just create.

  FYI - This is also where you can change it to another view.
