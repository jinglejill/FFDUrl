//
//  DocumentBrowserViewController.h
//  FFDUrl
//
//  Created by Thidaporn Kijkamjai on 4/6/2561 BE.
//  Copyright © 2561 Appxelent. All rights reserved.
//

#import <UIKit/UIKit.h>

@interface DocumentBrowserViewController : UIDocumentBrowserViewController

- (void)presentDocumentAtURL:(NSURL *)documentURL;

@end
