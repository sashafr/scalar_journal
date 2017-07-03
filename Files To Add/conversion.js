/*
 *  conversion.js
 *  File to parse and convert JSON objects produced by 
 *  Scalar to PDF
 *  Author - Joe Pires
 *  Version - 1.0.0
 */

/* As a general overview, all of the generate... classes follow
 * the same pattern:
 * Generate the object to put into docDef
 * Filter the text appropriately
 * Fill out the object
 */

function filterOut(descriptionString) {
  if (descriptionString.includes("DescTags:")) {
    descriptionString = descriptionString.replace(/DescTags: .*/, "");
  } else if (descriptionString.includes("Desc Tags:")) {
    descriptionString = descriptionString.replace(/Desc Tags: .*/, "");
  } else if (descriptionString.includes("Description Tags:")) {
    descriptionString = descriptionString.replace(/Description Tags: .*/, "");
  } else if (descriptionString.includes("Tags:")) {
    descriptionString = descriptionString.replace(/Tags: .*/, "");
  } else {
    
  }
  return descriptionString;
}

function generateListObject(tagString, tagType) {
  var listObject = {};
  var regex = /<span[^>]*>|<\/span>|<\/li>|<\/ol>|<\/ul>|<ol>|<ul>|&nbsp;/g;
  tagString = tagString.replace(regex, '');
  if (tagType === "ul") {
    listObject = {
      ul: []
    };
  } else {
    listObject = {
      ol: []
    };
  }
  var listString = "";
  for (var a = 0; a < tagString.length; a++) {
    if (tagString.substring(a, a + 4) == "<li>") {
      if (listString.length != 0) {
        if (tagType === "ol") {
          listString = listString.replace("li>", "");
          listObject.ol.push(listString);
          listString = "";
        } else {
          listString = listString.replace("li>", "");
          listObject.ul.push(listString);
          listString = "";
        }
      }
    } else {
      listString = listString + tagString.charAt(a);
    }
  }
  if (tagType === "ol") {
    listString = listString.replace("li>", "");
    listObject.ol.push(listString);
  } else {
    listString = listString.replace("li>", "");
    listObject.ul.push(listString);
  }
  return listObject;
};

function generateBlockQuoteObject(tagString) {
  var blockQuoteRegex = /<blockquote>|<p>|<\/blockquote>|<\/p>|blockquote>/g;
  tagString = tagString.replace(blockQuoteRegex, '');
  var blockObject = {
    text: tagString,
    style: 'quoteFormat'
  };
  return blockObject;
};

function generatePreObject(tagString) {
  var regex = /<pre>|<\/pre>/g;
  tagString = tagString.replace(regex, '');
  var returnObject = { text: tagString, style: "pre" };
  return returnObject;
};

function generateLinkObject(tagString) {
  tagString = tagString.replace('<a href="', '');
  var findQuote = tagString.indexOf('"');
  var linkVal = tagString.substring(0, findQuote);
  tagString = tagString.replace(linkVal + '">', '');
  var linkTextPos = tagString.indexOf('>');
  linkText = tagString.substring(0, linkTextPos);
  linkText = linkText.replace("</a", '');
  var linkObject = {
    text: linkText,
    link: linkVal
  };
  return linkObject;
};

function filterContent(contentVal) {
  // I don't think you can have a mix of underlined, bold, and italic
  // text in the same paragraph
  // so I'll just filter it out for right now :(
  var filterRegex = /<\/u>|<u>|<em>|<\/em>|<strong>|<\/strong>|&nbsp;|<h1>|<h2>|<h3>|<h4>|<h5>|<h6>|<\/h1>|<\/h2>|<\/h3>|<\/h4>|<\/h5>|<\/h6>|\n/g;
  contentVal = contentVal.replace(filterRegex, '');
  var objectList = [];
  var listString = "";
  var pushObject;
  for (var i = 0; i <= contentVal.length; i++) {
    // Add final element to list and break if i === contentVal.length
    if (i === contentVal.length && listString !== "") {
      if (listString.substring(0, 4) === "<ul>") {
        pushObject = generateListObject(listString, "ul");
        objectList.push(pushObject);
      } else if (listString.substring(0, 4) === "<ol>") {
        pushObject = generateListObject(listString, "ol");
        objectList.push(pushObject);
      } else if (listString.substring(0, 7) === "<a href") {
        pushObject = generateLinkObject(listString);
        objectList.push(pushObject);
      } else if (listString.substring(0, 12) === "<blockquote>") {
          pushObject = generateBlockQuoteObject(listString);
          objectList.push(pushObject);
      } else if (listString.substring(0, 5) === "<pre>") {
          listString = listString.replace(/<pre>|<\/pre>/g, '');
          pushObject = generatePreObject(listString);
          objectList.push(pushObject);
      } else {
        listString = listString.replace(filterRegex, '');
        listString = listString.replace('br />', '');
        var listStringObject = { text: listString, style: 'body' };
        objectList.push(listStringObject);
      }
      break;
    } else if (i === contentVal.length && listString === "") {
      break;
    }

    // Unordered Lists
    if (contentVal.substring(i, i + 4) === "<ul>") {
      if (listString.length !== 0) {
        listString = listString.replace('br />', '');
        pushObject = { text: listString, style: 'body' };
        objectList.push(pushObject);
        listString = "";
      }
      for (var p = i; p <= contentVal.length; p++) {
        if (listString.length > 5 && listString.substring(listString.length - 5, listString.length) === "</ul>") {
          pushObject = generateListObject(listString, "ul");
          contentVal = contentVal.replace(listString, '');
          listString = "";
          objectList.push(pushObject);
          break;
        }
        if (p === contentVal.length) {
          contentVal = contentVal.replace(listString, '');
          listString = "";
          break;
        } else {
          listString = listString + contentVal.charAt(p);
        }
      }
    }

    // Ordered Lists
    if (contentVal.substring(i, i + 4) === "<ol>") {
      if (listString.length !== 0) {
        listString = listString.replace('br />', '');
        pushObject = { text: listString, style: 'body' };
        objectList.push(pushObject);
        listString = "";
      }
      for (var p = i; p <= contentVal.length; p++) {
        if (listString.length > 5 && listString.substring(listString.length - 5, listString.length) === "</ol>") {
          pushObject = generateListObject(listString, "ol");
          contentVal = contentVal.replace(listString, '');
          listString = "";
          objectList.push(pushObject);
          break;
        }
        if (p === contentVal.length) {
          listString = "";
          break;
        } else {
          listString = listString + contentVal.charAt(p);
        }
      }
    }

    // Links (I think I can only do paragraphs of links, but I'll try to play around with it)
    if (contentVal.substring(i, i + 7) === "<a href") {
      if (listString.length !== 0) {
        listString = listString.replace('br />', '');
        pushObject = { text: listString, style: 'body' };
        objectList.push(pushObject);
        listString = "";
      }
      for (var p = i; p < contentVal.length; p++) {
        if (listString.length > 4 && listString.substring(listString.length - 4, listString.length) === "</a>") {
          pushObject = generateLinkObject(listString);
          contentVal = contentVal.replace(listString, '');
          listString = "";
          objectList.push(pushObject);
          break;
        } else {
          listString = listString + contentVal.charAt(p);
        }
      }
    }

    // Blockquote Objects
    if (contentVal.substring(i, i + 12) === "<blockquote>") {
      if (listString.length !== 0) {
        pushObject = { text: listString, style: 'body' };
        objectList.push(pushObject);
        listString = "";
      }
      for (var u = i; u < contentVal.length; u++) {
        if (listString.length > 17 && listString.substring(listString.length - 13, listString.length - 17) === "</p>") {
          pushObject = generateBlockQuoteObject(listString);
          contentVal = contentVal.replace(listString, '');
          listString = "";
          objectList.push(pushObject);
          break;
        } else {
          listString = listString + contentVal.charAt(u);
        }
      }
    }

    // Pre Objects
    if (contentVal.substring(i, i + 5) === "<pre>") {
      if (listString.length !== 0) {
        pushObject = { text: listString, style: 'body' };
        objectList.push(pushObject);
        listString = "";
      }
      for (var q = i; q < contentVal.length; q++) {
        if (listString.length > 6 && listString.substring(listString.length - 6, listString.length) === "</pre>") {
          listString = listString.replace(/<pre>|<\/pre>/g, '');
          pushObject = generatePreObject(listString);
          contentVal = contentVal.replace(listString, '');
          listString = "";
          objectList.push(pushObject);
          break;
        } else {
          listString = listString + contentVal.charAt(q);
        }
      }
    }

    // The breaklines for the paragraphs
    if (contentVal.substring(i, i + 6) === "<br />") {
      listString = listString.replace(filterRegex, '');
      listString = listString.replace('br />', '');
      var listStringObject = { text: listString, style: 'body' };
      objectList.push(listStringObject);
      listString = "";
      contentVal = contentVal.replace("<br />", "");
    } else {
      listString = listString + contentVal.charAt(i);
    }
  }

  return objectList;
};

function convert(JSONString, authorName, urlString) {
  /* As for the process, we need to extract the title,
   * author, description (if there is one, so MAKE THAT OPTIONAL),
   * content, metadata (how are we adding that), and citations
   * (this should be done in the form of a list).
   * To do that, parse through the JSON object and the objects 
   * within the JSON object. Then, separate the content by its 
   * paragraphs (I'm assuming that the tag for paragraphs is just <br />),
   * then form the PDF object, then run it through pdfmake.
   * Right now, it just downloads the PDF, but we'll need it to also show the pdf.
   */


  /* Extract the title and author so I can actually access the JSON by string
   * First get a stringified form of the JSON object
   * Then remove the http://dev.upenndi... part until we get to the / 
   * past the "scalar" part of the URL
   * then, until we get to the point where the next section of the stringified
   * JSON is "/index" (do that for right now and hope to God that no one has that in 
   * their title), extract tht title
   */
  JSONString = JSON.stringify(JSONString);
  var JSONObj = JSON.parse(JSONString);
  if (Object.keys(JSONObj).length === 0 && JSONObj.constructor === Object) {
    alert("Sorry. We can't convert. There is no content.");
    return;
  }
  JSONString = JSONString.replace("{\"" + urlString, "");
  var titleAuthor = "";
  // This is not a good way to access the title and author
  // Needs to be more detailed
  // JP
  for (var w = 0; w < JSONString.length; w++) {
    if (JSONString.charAt(w) === '/') {
      break;
    } else {
      titleAuthor = titleAuthor + JSONString.charAt(w);
    }
  }
  console.log(titleAuthor);


  // Version and Time It Went Live (I'll Need The Version Number For The Next Part)
  var jsonTitleString = urlString + titleAuthor + "/index";
  var JSONTitleObject = JSONObj[jsonTitleString];
  var liveList = JSONTitleObject["http://purl.org/dc/terms/created"];
  var liveIntermediary = liveList[0];
  var wentLive = liveIntermediary["value"];

  var numberRegex = /\d+/g;

  var versionList = JSONTitleObject["http://purl.org/dc/terms/hasVersion"];
  var versionIntermediary = versionList[0];
  var versionString = versionIntermediary["value"];
  var versionObject = versionString.match(numberRegex);
  var versionNumber = versionObject[versionObject.length - 1];

  // Getting the user number (but not the user) 
  // This should help in getting the actual name of the user

  var extractUserList = JSONTitleObject["http://www.w3.org/ns/prov#wasAttributedTo"];
  var extractUserIntermediary = extractUserList[0];
  var extractUserString = extractUserIntermediary["value"];
  var extractUserObject = extractUserString.match(numberRegex);
  var extractUserNumber = extractUserObject[extractUserObject.length - 1];

  // Title, Description, and Content
  var descAndContString = urlString + titleAuthor + "/index." + versionNumber;
  var JSONDescAndCont = JSONObj[descAndContString];
  var JSONDescriptionList = JSONDescAndCont["http://purl.org/dc/terms/description"];

  var JSONContentList = JSONDescAndCont["http://rdfs.org/sioc/ns#content"];
  var descriptionIntermediary = JSONDescriptionList[0];
  var contentIntermediary = JSONContentList[0];
  var description = descriptionIntermediary["value"];
  description = filterOut(description);
  var content = contentIntermediary["value"];
  var titleList = JSONDescAndCont["http://purl.org/dc/terms/title"];
  var titleIntermediary = titleList[0];
  var title = titleIntermediary["value"];

  var contentList = filterContent(content);

  /* Creating the docDef object. Features:
   * Letter paper with proper spacing
   * Footer for the page number
   * Name of Author...
   * Then Title...
   * Then Description (If there is one)...
   * Then Content (Which will probably include references)
   * Each section (and the paragraphs of the content section) will
   * be separated by a little space for readability
   */
  var docDef = {
    pageSize: 'LETTER',

    pageMargins: [50, 50, 50, 50],

    footer: function footer(currentPage, pageCount) {
      return "Page: " + currentPage.toString() + ' of ' + pageCount;
    },

    content: [
    { text: authorName, style: "name" }, 
    { text: " ", style: "spacing" }, 
    { text: title, style: "title" }, 
    { text: " ", style: "spacing" }, 
    { text: "Description: " + description, style: "description" }, 
    { text: " ", style: "spacing" }],

    styles: {
      /* Need to speak with Sasha regrding the design of the pdf
       * These are just baseline things, just so I could quickly
       * get to the PDF generation part of my work
       */
      title: {
        fontSize: 24,
        bold: true,
        alignment: 'center'
      },
      name: {
        fontSize: 14,
        bold: true,
        alignment: 'center'
      },
      description: {
        fontSize: 12,
        bold: false,
        alignment: 'left'
      },
      body: {
        fontSize: 16,
        bold: false,
        alignment: 'left'
      },
      bold: {
        fontSize: 16,
        bold: true,
        alignment: 'left'
      },
      italics: {
        fontSize: 16,
        italics: true,
        alignment: 'left'
      },
      bold: {
        fontSize: 16,
        bold: true,
        alignment: 'left'
      },
      spacing: {
        fontSize: 6,
        alignment: 'center'
      },
      preFormat: {
        fontSize: 10,
        alignment: 'center',
        bold: false
      },
      quoteFormat: {
        fontSize: 12,
        alignment: 'center',
        bold: true
      },
      h1: {
        fontSize: 20,
        alignment: 'center',
        bold: true
      },
      h2: {
        fontSize: 18,
        alignment: 'center',
        bold: true
      },
      h3: {
        fontSize: 16,
        alignment: 'center',
        bold: true
      },
      h4: {
        fontSize: 14,
        alignment: 'center',
        bold: true
      },
      h5: {
        fontSize: 12,
        alignment: 'center',
        bold: true
      },
      h6: {
        fontSize: 10,
        alignment: 'center',
        bold: true
      }
    }
  };

  // Create the paragraphs
  for (var m = 0; m < contentList.length; m++) {
    docDef.content.push(contentList[m]);
    var spacingObject = { text: " ", style: "spacing" };
    docDef.content.push(spacingObject);
  }
  // Is there a specific process for going to ScholarlyCommons?
  var fileString = authorName + " - " + title + ".pdf";
  pdfMake.createPdf(docDef).download(fileString);
};
