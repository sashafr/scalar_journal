Scalar (University of Pennsylvania)
======

Congratulations on discovering Scalar, the next generation in media-rich, scholarly electronic publishing!

This is a revised and unofficial version of Scalar for the University of Pennsylvania. For the time being, there will be some extra instructions for those installing Scalar using the modified source code.

To begin using Penn's implementation of Scalar, go to http://dev.upenndigitalscholarship.org/scalar/

To learn more about the added features and changes in this variant of Scalar, go to the section of the readme labeled <b>New Features</b>

For more information about Scalar in general, learn more at http://scalar.usc.edu/scalar/

To install Scalar on your own server, you can download the most recent build from GitHub. Or, if you are concerned about downloading from the "live" GitHub codebase, we periodically create a GitHub Release. The code kept in a release can be assumed to be tested in both development and live environments (e.g., scalar.usc.edu).  For help installing Scalar, see either INSTALL.txt or UPDATE.txt in the project root folder.

If you'd like to add in the extra features, go to the section of the readme labeled <b>Adding Extras</b>

Periodically, changes are made to Scalar's config files or database structure. If you have installed Scalar on your own server and are planning to update from GitHub, you'll likely need to make updates to your local config files or database. Changes are being tracked on the wiki: https://github.com/anvc/scalar/wiki/Changes-to-config-files-over-time/.

### New Features

<b>PDF Conversion</b>

Those with access to articles (either as a superuser or as the author of the article) can convert articles to PDF, which can be sent to Scholarly Commons.

It's a fairly simple process. There is a button (or link) labeled Convert To PDF. Clicking that will extract the JSON version of the article and convert it to PDF.

Due to the libraries being used, some of the multimedia aspects of the article will not be shown. For instance, there will be no images or other forms of media aside from text. There will be links to external wabpages. 

As of right now, basic text, unordered lists, ordered lists, blockquotes, preformatted text (like code), and links (if they're in their own paragraph) will be properly processed. Given the libraries that we're using (pdfmake, specifically), formatted text and links within paragraphs could not be done easily. 

There also needs to be some testing, especially with multi-page books.

Hopefully it should be up soon.

<b>Table of Contents</b>

For all of the books in the database, there will be a table of contents for all the published books. This, like the articles themselves, will be in book form.

If there isn't a Table of Contents already, go to the dashboard and create one (there will be a button that says "Generate Table of Contents." This will generate the book for the table of contents, as well as the contents for what's published.

If you wish to update it, go to the dashboard and update it there.

To generate the page for the table of contents, go to the import/export tab in the dashboard and click the bottom button to generate the page.

UPDATE 8 JUNE 2017 - Instead of a book form of the table of contents, the table of contents will only be on the index page of scalar. There, all books made public will be shown there, along with the date created, the author, and the description. All of the code for the book version will be commented out. If someone would like to bring that functionality back, they are more than welcome to do so.

<b>Changes to Structure of Scalar</b>

For our implementation of Scalar, instead of a series of articles per book, each book will have one article in it, and the collection of books will form a volume. When new page buttons are clicked, they won't do anything, other than give a message saying that there can only be one page (article) per book.

Places Where This Is Implemented:
* Adding a new page when editing the book
* Adding a new page in the dashboard
* Importing from another book, if your book isn't empty (if it has content, the JSON object for the book will not be empty and importing will add another page)

In all honesty, in terms of functionality, is this a good idea?

<b>Changes to Language in Scalar</b>

To get closer to what we want to show in Scalar, the language throughout Scalar has changed to reflect the roles we wanted to utilize. Mainly, changes have been made to account for the Author, Reviewer, and Editor role. Authors have similar roles to what they have now. Editors now take the place of commenters, and may have special privileges (since they're engaging in peer reviews). As for editors, those changes will have to be discussed and implemented. From what I can tell, editors will have super-admin priveleges once they register.

<b>Versioning</b>

You can create a new version of your article, and keep the old version. In the dashboard (in the My Account tab specifically), you can generate a new version of the article you were working on by clicking on the "Generate New Version" button associated with the article.

Given Scalar's structure right now, this will have to be done in two parts. The first part is the autogeneration of the articles (this is in the My Account part of the dashboard). The second part is the transferral from the original article to the new version (this is in the transfer tab).

EDIT 14 JUNE 2017 - Actually, Scalar has that functionality, so we're good with what we have. Unless Peter wants something different. 

I did do some modifications to the dashboard code, however. This is mainly to ensure that all of a user's books (and not just all duplicatable books) can be duplicated. Things are commented appropriately.

<b>Peer Reviews</b>

This will be used in lieu of comments. Those with editor priveleges will be able to comment. These will be the bases for the peer reviews. In regard to implementation, I haven't gotten there yet.

<b>Filtering By Tags (Description) </b>

To increase the search capabilities of Scalar, and to keep in line with the blog style that Peter wants, we've implemented the use of search tags. For sake of getting things to work, you put the tags at the end of the description, and while searching, Scalar looks at any article with that tag (or if the article's title has that keyword)

<b>Passwords</b>

We're assuming that Peter will have to create the accounts. We're also assuming that he wants secure passwords. We're also assuming that he won't want to create those passwords. So, we automated the password generation process. Since they're randomized, make sure that the user writes down the password.

<b>Known Issues</b>

Although not much of a concern given the one-article-per-book policy, multi-page books have not been tested for PDF conversion. Based of the code, they will only do the first page of a book.

Don't put html tags in the custom CSS box. It will cause issue for the import/export tab in the dashboard and to the PDF conversion. If you don't follow warnings and do it anyway, don't be surprised if things get wonky.

For one reason or another, parts of the code enforcing the one-article-per-book policy just don't work. Those will hopefully be fixed by the end of June.

For now, let's not have any of the titles have a forward slash "/." I need to go back in to the pdf conversion file (conversion.js) and make that more robust.

Speaking of PDF conversion, I know that not all tags are being handled. Those will be handled promptly. If you encounter any issues with tags, contact me at piresjo@seas.upenn.edu

I don't expect this to happen (and that's why I designed the filtering by description tags this way), but if you do write a description, don't put in "DescTags:" unless you're adding search tags

<b> If Updates to Scalar Need to be Made </b>

I have commented all of the parts that I have edited. All of those comments have my name in them. To find the parts that I worked on, just search for 'JP' in the code.

For the exact files, feel free to to look at the github repository. The files that I edited

Also, since there are some files that I thought would need editing (but didn't), at the end of all of this (late June), I'll have a list of files that I edited for each feature, and a description of what I did.

### Installing New Features

As mentioned before, this variant of Scalar has new features. Unfortunately, given the current nature of Scalar, many of the new features have to be added into the base code. The files changed are in the section below. As mentioned before, I commened the sections that I edited with the letters JP. Find those sections in the old codebase, and copy it over to the files in the new codebase.

Yes, I know that it's a tedious process for whoever is setting a new version up. Once Scalar fixes its hook and plugin functionality, much of the code will change so that a lot of these features will come in the form of plugins.

### List of Files Changed

<b> PDF Conversion </b>
 - conversion.js - New file, converts RDF-JSON object to a workable PDF for Scholarly Commons
 - pdfmake.js - Used to actually create the PDF
 - transfer_pi.php - Edits to this file regarding generating a PDF include adding a button to generate the PDF on command, accessiong the RDF-JSON object and storing it in a hidden pre tag, and linking the button with the logic to create the PDF.

<b> Documentation (Only Found On Github) </b>
 - covfefe.md - General information about the changes
 - filesChanged.md - This file

<b> Changing Language In Scalar </b>
 - scalarcomments.jquery.js - All mention of comments have been changed to peer reviews
 - reply_list.php - All mention of comments have been changed to peer reviews

<b> Table of Contents </b>
 - book_list.css - Chnages were made to the index page's design
 - book_list.php - For public books, description is now shown. All books (public and private) are ordered chronologically and show author and date published. Furthermore, the view button for public books has been commented out; we'll just show the public books automatically.
 - index.html - Before idea was scrapped, we were planning to generate a book form of the table of contents. This is the index page for the import/export tab in the Scalar dashboard. This would be used to help create a JSON object for the Table of Contents, and then transfer it over to the newly made Table of Contents book.
 - system.php - Some code was modified to automatically show public books.
 - user.php - Originally, we had the idea of creating a book form of the Table of Contents. This turned out to be a process that couldn't be done in one action. The first part was the generation of a Table of Contents book. Code for the My Account tab of the Dashboard was modified to allow that to happen.

<b> Versions of Books </b>
 - user.php - The dashboard actually allows for duplicating books, which is the lynchpin for how Dr. Cobb wants versioning (at least based on what I heard). However, now you can duplicate all of your books, and not just books that can be duplicatable. I might want to go back and add this feature for all public books as well. 
 - sharing.php - Commented out the part of the sharing tab website pertaining to duplicating a book
 - user_book.php - Commented out section that checked to see if a book could be duplicated, allowing for all of the user's books to be duplicated with ease.

<b> Structural Changes (Book Policy) </b>

 - pages.php - There's a button to create a new page. That has been disabled.
 - scalarheader.jquery.js - Tried to prevent a new page when editing the book. 
 - scalarheader.jquery.js (aclsworkbench folder) - Disabled ability to add new page 
 - index.html (transfer folder) - Made changes so that if you're importing, you can only import to an empty book. Otherwise, there will be multiple pages, and that goes against the policy that I wanted.

<b> Filtering By Tags (Description Search Tags) </b>
 - book_model.php - Get description from each book, get the tags from the description, and filter accordingly
 - book_list.php - If we're doing this through the description, then the description tags section needs to be filtered out (it'd be unsightly if we kept it in). So, it's filtered out. 

<b> Account Stuff </b>
 - all_users.php - Autogenerate complex passwords