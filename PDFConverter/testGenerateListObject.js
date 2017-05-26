function generateListObject(tagString, tagType) {
  // For right now, forget about the info in the span
  // Later on, however, fix it
  var listObject = {};
  var regex = /<s*>|<\/span>|<\/li>|<\/ol>|<\/ul>|<ol>|<ul>|&nbsp;/g;
  /*tagString = tagString.replace(/<s*>/g, ''); // tried a regex; nothing worked; will have to test again*/
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
    if (tagString.substring(a, a+4) == "<li>") {
      if (listString.length != 0) {
        if (tagType === "ol") {
          listString = listString.replace("li>", "");
          listObject.ol.push(listString);
          listString = "";
        } else {
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

var testOrderedListString = `<ol><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. </span></li><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Donec quis neque eget tortor pulvinar malesuada at mollis odio. </span></li><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Maecenas interdum velit dui, eu faucibus leo interdum eu.</span></li></ol>`;

var testOrderedResult = generateListObject(testOrderedListString, "ol");
console.log(testOrderedResult);

var testUnorderedListString = `<ul><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec quis neque eget tortor pulvinar malesuada at mollis odio. </span></li><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Maecenas interdum velit dui, eu faucibus leo interdum eu. Lorem ipsum dolor sit amet, consectetur adipiscing elit. </span></li><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Morbi consectetur, neque laoreet tristique maximus, turpis nulla mollis arcu, vel sagittis libero arcu nec nisl.&nbsp;</span></li></ul>`;

var testUnorderedResult = generateListObject(testUnorderedListString, "ul");
console.log(testUnorderedResult);
