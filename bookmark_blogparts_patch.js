if (typeof HBBlogParts != 'undefined') {
	(function ($) {
		var me = this;
		// override function
		var catchJSON = this.catchJSON;
		if (typeof(catchJSON) == 'function') {
			this.catchJSON = function(entry) {
				if (options.hideNoBookmark) {
					if (!entry) return;
				}
				catchJSON.apply(this,[entry]);
			}
		}
		// set option
		var options = WPHatenaBookmarkComment;
		this.debug = (options.debug === 'true');
		this.commentInsertSelector = options.commentInsertSelector.split(',');
		this.insertPosition = options.insertPosition;
		this.Design = options.Design.split(',');
		this.useUserCSS = (options.useUserCSS === 'true');
		this.permalinkURI = options.permalinkURI;
		this.listPageCommentLimit = parseInt(options.listPageCommentLimit);
		this.permalinkCommentLimit = parseInt(options.permalinkCommentLimit);
		this.permalinkSelector = options.permalinkSelector.split(',');
		this.permalinkAttribute = 'href';
		this.permalinkPathRegexp = new RegExp(options.permalinkPathRegexp);
	}).apply(HBBlogParts, [jQuery]);
}