Here is a description of the various changes done to the system.

-- PDF Conversion --

Those with access to articles (either as a superuser or as the author of the article) can convert articles to PDF, which can be sent to Scholarly Commons.

It's a fairly simple process. There is a button (or link) labeled Convert To PDF. Clicking that will extract the JSON version of the article and convert it to PDF.

Due to the libraries being used, some of the multimedia aspects of the article will not be shown. For instance, there will be no images or other forms of media aside from text. There will be links to external wabpages. 

As of right now, basic text, unordered lists, ordered lists, blockquotes, preformatted text (like code), and links (if they're in their own paragraph) will be properly processed. Given the libraries that we're using (pdfmake, specifically), formatted text and links within paragraphs could not be done easily. 

There also needs to be some testing, especially with multi-page books.

Hopefully it should be up soon.

-- Table of Contents --

For all of the books in the database, there will be a table of contents for all the published books. This, like the articles themselves, will be in book form.

If there isn't a Table of Contents already, go to the dashboard and create one (there will be a button that says "Generate Table of Contents." This will generate the book for the table of contents, as well as the contents for what's published.

If you wish to update it, go to the dashboard and update it there.

To generate the page for the table of contents, go to the import/export tab in the dashboard and click the bottom button to generate the page.

UPDATE 8 JUNE 2017 - Instead of a book form of the table of contents, the table of contents will only be on the index page of scalar. There, all books made public will be shown there, along with the date created, the author, and the description. All of the code for the book version will be commented out. If someone would like to bring that functionality back, they are more than welcome to do so.

-- Changes to Structure of Scalar --

For our implementation of Scalar, instead of a series of articles per book, each book will have one article in it, and the collection of books will form a volume. When new page buttons are clicked, they won't do anything, other than give a message saying that there can only be one page (article) per book.

Places Where This Is Implemented:
	- Adding a new page when editing the book
	- Adding a new page in the dashboard
	- Importing from another book, if your book isn't empty (if it has content, the JSON object for the book will not be empty and importing will add another page)

In all honesty, in terms of functionality, is this a good idea?

-- Changes to Language in Scalar --

To get closer to what we want to show in Scalar, the language throughout Scalar has changed to reflect the roles we wanted to utilize. Mainly, changes have been made to account for the Author, Reviewer, and Editor role. Authors have similar roles to what they have now. Editors now take the place of commenters, and may have special privileges (since they're engaging in peer reviews). As for editors, those changes will have to be discussed and implemented. From what I can tell, editors will have super-admin priveleges once they register.

-- Versioning --

You can create a new version of your article, and keep the old version. In the dashboard (in the My Account tab specifically), you can generate a new version of the article you were working on by clicking on the "Generate New Version" button associated with the article.

Given Scalar's structure right now, this will have to be done in two parts. The first part is the autogeneration of the articles (this is in the My Account part of the dashboard). The second part is the transferral from the original article to the new version (this is in the transfer tab).

EDIT 14 JUNE 2017 - Actually, Scalar has that functionality, so we're good with what we have. Unless Peter wants something different.

-- Peer Reviews --

This will be used in lieu of comments. Those with editor priveleges will be able to comment. These will be the bases for the peer reviews. In regard to implementation, I haven't gotten there yet.

-- If Updates to Scalar Need to be Made --

I have commented all of the parts that I have edited. All of those comments have my name in them. To find the parts that I worked on, just search for 'JP' in the code.

For the exact files, feel free to to look at the github repository. The files that I edited

Also, since there are some files that I thought would need editing (but didn't), at the end of all of this (late June), I'll have a list of files that I edited for each feature, and a description of what I did.

