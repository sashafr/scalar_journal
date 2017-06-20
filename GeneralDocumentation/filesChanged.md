List of Files Changed

-- PDF Conversion --
 - conversion.js - New file, converts RDF-JSON object to a workable PDF for Scholarly Commons
 - pdfmake.js - Used to actually create the PDF
 - transfer_pi.php - Edits to this file regarding generating a PDF include adding a button to generate the PDF on command, accessiong the RDF-JSON object and storing it in a hidden pre tag, and linking the button with the logic to create the PDF.

-- Documentation (Only Found On Github) --
 - covfefe.md - General information about the changes
 - filesChanged.md - This file

-- Changing Language In Scalar --
 - scalarcomments.jquery.js - All mention of comments have been changed to peer reviews
 - reply_list.php - All mention of comments have been changed to peer reviews

-- Table of Contents --
 - book_list.css - Chnages were made to the index page's design
 - book_list.php - For public books, description is now shown. All books (public and private) are ordered chronologically and show author and date published. Furthermore, the view button for public books has been commented out; we'll just show the public books automatically.
 - index.html - Before idea was scrapped, we were planning to generate a book form of the table of contents. This is the index page for the import/export tab in the Scalar dashboard. This would be used to help create a JSON object for the Table of Contents, and then transfer it over to the newly made Table of Contents book.
 - system.php - Some code was modified to automatically show public books.
 - user.php - Originally, we had the idea of creating a book form of the Table of Contents. This turned out to be a process that couldn't be done in one action. The first part was the generation of a Table of Contents book. Code for the My Account tab of the Dashboard was modified to allow that to happen.

-- Versions of Books --
 - user.php - The dashboard actually allows for duplicating books, which is the lynchpin for how Dr. Cobb wants versioning (at least based on what I heard). However, now you can duplicate all of your books, and not just books that can be duplicatable. I might want to go back and add this feature for all public books as well. 
 - sharing.php - Commented out the part of the sharing tab website pertaining to duplicating a book
 - user_book.php - Commented out section that checked to see if a book could be duplicated, allowing for all of the user's books to be duplicated with ease.

-- Structural Changes (Book Policy) --
 - Based on what Dr. Cobb wanted, books cannot have more than one article in them.
 - pages.php - There's a button to create a new page. That has been disabled.
 - scalarheader.jquery.js - Tried to prevent a new page when editing the book. 
 - scalarheader.jquery.js (aclsworkbench folder) - Disabled ability to add new page 
 - index.html (transfer folder) - Made changes so that if you're importing, you can only import to an empty book. Otherwise, there will be multiple pages, and that goes against the policy that I wanted.

-- Filtering By Tags (Description Search Tags) --
 - book_model.php - Get description from each book, get the tags from the description, and filter accordingly
 - book_list.php - If we're doing this through the description, then the description tags section needs to be filtered out (it'd be unsightly if we kept it in). So, it's filtered out. 

-- Filtering By Tags (Scalar Tags) --

-- Account Stuff --
 - all_users.php - Autogenerate complex passwords
