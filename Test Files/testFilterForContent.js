function generateListObject(tagString, tagType) {
      // For right now, forget about the info in the span
      // Later on, however, fix it
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
        if (tagString.substring(a, a+4) == "<li>") {
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
      var returnObject = {text: tagString, style: "pre"};
      return returnObject;
    };

    function generateLinkObject (tagString) {
      tagString = tagString.replace('<a href="', '');
      var findQuote = tagString.indexOf('"');
      var linkVal = tagString.substring(0, findQuote);
      tagString = tagString.replace(linkVal+'">', '');
      var linkTextPos = tagString.indexOf('>');
      linkText = tagString.substring(0,linkTextPos);
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
      var filterRegex = /<\/u>|<u>|<em>|<\/em>|<strong>|<\/strong>/g;
      var objectList = [];
      var listString = "";
      var pushObject;
      for (var i = 0; i <= contentVal.length; i++) {
        // Add final element to list and break if i === contentVal.length
        if (i === contentVal.length && listString !== "") {
          listString = listString.replace('br />', '');
          listString = listString.replace(filterRegex, '');
          pushObject = {text: listString, style: 'body'};
          objectList.push(pushObject);
          break;
        } else if (i ===contentVal.length && listString === "") {
          break;
        }


        // Unordered Lists
        if (contentVal.substring(i, i+4) === "<ul>") {
          if (listString.length !== 0) {
            listString = listString.replace('br />', '');
            pushObject = {text: listString, style: 'body'};
            objectList.push(pushObject);
            listString = "";
          }
          for (var p = i; p <= contentVal.length; p++) {
            if (listString.length > 5 && 
              listString.substring(listString.length - 5, listString.length) === "</ul>") {
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
        if (contentVal.substring(i, i+4) === "<ol>") {
          if (listString.length !== 0) {
            listString = listString.replace('br />', '');
            pushObject = {text: listString, style: 'body'};
            objectList.push(pushObject);
            listString = "";
          }
          for (var p = i; p <= contentVal.length; p++) {
            if (listString.length > 5 && 
              listString.substring(listString.length - 5, listString.length) === "</ol>") {
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
        if (contentVal.substring(i, i+7) === "<a href") {
          if (listString.length !== 0) {
            listString = listString.replace('br />', '');
            pushObject = {text: listString, style: 'body'};
            objectList.push(pushObject);
            listString = "";
          }
          for (var p = i; p < contentVal.length; p++) {
            if (listString.length > 4 && 
              listString.substring(listString.length - 4, listString.length) === "</a>") {
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
        //console.log(contentVal.substring(i, i+12));
        if (contentVal.substring(i, i+12) === "<blockquote>") {
          console.log("BINGO");
          console.log(" ");
          console.log(" ");
          if (listString.length !== 0) {
            pushObject = {text: listString, style: 'body'};
            objectList.push(pushObject);
            listString = "";
          }
          for (var u = i; u < contentVal.length; u++) {
            if (listString.length > 17 && 
              listString.substring(listString.length-13, listString.length-17) === "</p>") {
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
        if (contentVal.substring(i, i+5) === "<pre>") {
          if (listString.length !== 0) {
            pushObject = {text: listString, style: 'body'};
            objectList.push(pushObject);
            listString = "";
          }
          for (var q = i; q < contentVal.length; q++) {
            if (listString.length > 6 && 
              listString.substring(listString.length - 6, listString.length) === "</pre>") {
              pushObject = generatePreObject(listString);
              listString = "";
              objectList.push(pushObject);
              break;
            } else {
              listString = listString + contentVal.charAt(q);
            }
          }
        }
        
        // The breaklines for the paragraphs
        // I need a better way to get rid 
        if (contentVal.substring(i, i+6) === "<br />") {
          listString = listString.replace(filterRegex, '');
          listString = listString.replace('br />', '');
          var listStringObject = {text: listString, style: 'body'};
          objectList.push(listString);
          listString = "";
          contentVal = contentVal.replace("<br />", "");
        } else {
          listString = listString + contentVal.charAt(i);
        }
      }

      return objectList;
    };
var testStringBeginning = `Lorem ipsum dolor sit amet, modo noster aliquid an mea. Eu vim nominati democritum, has quando iisque cu. Id eos falli corrumpit disputando, mea ex vide delectus. Postea vocibus at vix, nullam forensibus est eu. Pri eu brute fuisset noluisse, ut vix assum complectitur. Nibh everti efficiantur ut duo, eum tale agam delicatissimi eu.<ol><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. </span></li><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Donec quis neque eget tortor pulvinar malesuada at mollis odio. </span></li><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Maecenas interdum velit dui, eu faucibus leo interdum eu.</span></li></ol>`;
var testStringFirstList = `Lorem ipsum dolor sit amet, modo noster aliquid an mea. Eu vim nominati democritum, has quando iisque cu. Id eos falli corrumpit disputando, mea ex vide delectus. Postea vocibus at vix, nullam forensibus est eu. Pri eu brute fuisset noluisse, ut vix assum complectitur. Nibh everti efficiantur ut duo, eum tale agam delicatissimi eu.<ol><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. </span></li><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Donec quis neque eget tortor pulvinar malesuada at mollis odio. </span></li><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Maecenas interdum velit dui, eu faucibus leo interdum eu.</span></li></ol>Est te sumo epicuri, ei vis exerci nonumes, ea mazim graece integre usu. Debet animal officiis nec te, nec atqui persius fuisset eu. Vel assum everti sententiae an. Diceret detracto voluptaria cu usu. At vis diam civibus suscipiantur.<ul><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec quis neque eget tortor pulvinar malesuada at mollis odio. </span></li><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Maecenas interdum velit dui, eu faucibus leo interdum eu. Lorem ipsum dolor sit amet, consectetur adipiscing elit. </span></li><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Morbi consectetur, neque laoreet tristique maximus, turpis nulla mollis arcu, vel sagittis libero arcu nec nisl.&nbsp;</span></li></ul>`;
var testStringHalfWay = `Lorem ipsum dolor sit amet, modo noster aliquid an mea. Eu vim nominati democritum, has quando iisque cu. Id eos falli corrumpit disputando, mea ex vide delectus. Postea vocibus at vix, nullam forensibus est eu. Pri eu brute fuisset noluisse, ut vix assum complectitur. Nibh everti efficiantur ut duo, eum tale agam delicatissimi eu.<ol><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. </span></li><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Donec quis neque eget tortor pulvinar malesuada at mollis odio. </span></li><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Maecenas interdum velit dui, eu faucibus leo interdum eu.</span></li></ol>Est te sumo epicuri, ei vis exerci nonumes, ea mazim graece integre usu. Debet animal officiis nec te, nec atqui persius fuisset eu. Vel assum everti sententiae an. Diceret detracto voluptaria cu usu. At vis diam civibus suscipiantur.<ul><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec quis neque eget tortor pulvinar malesuada at mollis odio. </span></li><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Maecenas interdum velit dui, eu faucibus leo interdum eu. Lorem ipsum dolor sit amet, consectetur adipiscing elit. </span></li><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Morbi consectetur, neque laoreet tristique maximus, turpis nulla mollis arcu, vel sagittis libero arcu nec nisl.&nbsp;</span></li></ul>At reque munere eam, ad evertitur comprehensam has. Pri altera labore in. Ius ea oratio dissentiunt. His ad etiam iudicabit, vim id falli nulla. Rebum nostro facilis ex sea, in enim volumus mel, ea vim tota legendos. Qui te nibh quodsi persequeris, cu viderer tacimates rationibus cum.<blockquote><p>This is a quote</p></blockquote>Etiam aperiam cum ei, pri oratio tamquam tacimates eu, erant omittam ex eos. Te his eros senserit. Dicunt inermis per ea, pri at vero mucius oportere. Delicata democritum in eum, novum voluptaria per ex.`;
var testStringFull = `Lorem ipsum dolor sit amet, modo noster aliquid an mea. Eu vim nominati democritum, has quando iisque cu. Id eos falli corrumpit disputando, mea ex vide delectus. Postea vocibus at vix, nullam forensibus est eu. Pri eu brute fuisset noluisse, ut vix assum complectitur. Nibh everti efficiantur ut duo, eum tale agam delicatissimi eu.<ol><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. </span></li><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Donec quis neque eget tortor pulvinar malesuada at mollis odio. </span></li><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Maecenas interdum velit dui, eu faucibus leo interdum eu.</span></li></ol>Est te sumo epicuri, ei vis exerci nonumes, ea mazim graece integre usu. Debet animal officiis nec te, nec atqui persius fuisset eu. Vel assum everti sententiae an. Diceret detracto voluptaria cu usu. At vis diam civibus suscipiantur.<ul><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec quis neque eget tortor pulvinar malesuada at mollis odio. </span></li><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Maecenas interdum velit dui, eu faucibus leo interdum eu. Lorem ipsum dolor sit amet, consectetur adipiscing elit. </span></li><li><span style=\"color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align: justify;\">Morbi consectetur, neque laoreet tristique maximus, turpis nulla mollis arcu, vel sagittis libero arcu nec nisl.&nbsp;</span></li></ul>At reque munere eam, ad evertitur comprehensam has. Pri altera labore in. Ius ea oratio dissentiunt. His ad etiam iudicabit, vim id falli nulla. Rebum nostro facilis ex sea, in enim volumus mel, ea vim tota legendos. Qui te nibh quodsi persequeris, cu viderer tacimates rationibus cum.<blockquote><p>This is a quote</p></blockquote>Etiam aperiam cum ei, pri oratio tamquam tacimates eu, erant omittam ex eos. Te his eros senserit. Dicunt inermis per ea, pri at vero mucius oportere. Delicata democritum in eum, novum voluptaria per ex.<br /><br /><a href=\"http://nintendo.com\">Link</a><br /><br /><strong>Velit sanctus suscipit ad vix, tibique assueverit referrentur ex his</strong>. <em>Amet vocent te mei, vis sapientem disputando et.</em> Ea has lorem <u>utroque consulatu, cu quis dico quo. Ne est modus vivendo dissentias</u>, per in atqui mucius noster, maiorum habemus eum an. No mei veniam prompta molestiae, per no quodsi minimum mentitum. Ad quod minim posidonium duo, legere eruditi habemus an vim, mel quod tibique ei.`;
var testList = filterContent(testStringFull);

console.log(testList);