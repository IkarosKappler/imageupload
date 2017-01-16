
// Define: plugin
(function($){
      hashtagifyThis = function () {
        var childNodes = this.childNodes,
            i = childNodes.length;
        while(i--)
        {
          var n = childNodes[i];
          if (n.nodeType == 3) {
            var html = $.trim(n.nodeValue);
            if (html)
              {
		  html = html.replace(/(^|\s)(#[a-z\d-]+)/ig, " $1<a href='$2' class='hash-tag'>$2</a> ");
              $(n).after(html).remove();
            }
          }
          else if (n.nodeType == 1  &&  !/^(a|button|textarea)$/i.test(n.tagName)) {
            hashtagifyThis.call(n);
          }
        }
      };

  $.fn.hashtagify = function () {
    return this.each(hashtagifyThis);
  };

})(jQuery);

