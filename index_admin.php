<?php

$content .= "<table><tr><td>";
$content .= "<h2>Pending Tasks</h2>";

$content .= "<div class='bigicon tenure'></div>";
$generalInformationTasks = $dataMgr->getGeneralInformationTasks('pending');
$content .= boxify($generalInformationTasks, 'approve', 'GeneralInformation');

$content .= "<div class='bigicon telephone'></div>";
$voiceServiceTasks = $dataMgr->getVoiceServiceTasks('pending');
$content .= boxify($voiceServiceTasks, 'approve', 'VoiceService');

$content .= "<div class='bigicon internalphonelistregistry'></div>";
$internalPhoneListRegistryTasks = $dataMgr->getInternalPhoneListRegistryTasks('pending');
$content .= boxify($internalPhoneListRegistryTasks, 'approve', 'InternalPhoneListRegistry');

$content .= "<div class='bigicon ubconlinedirectory'></div>";
$UBConlineDirectoryTasks = $dataMgr->getUBConlineDirectoryTasks('pending');
$content .= boxify($UBConlineDirectoryTasks, 'approve', 'UBConlineDirectory');

$content .= "<div class='bigicon sauderwebsitestaffdirectory'></div>";
$sauderWebsiteStaffDirectoryTasks = $dataMgr->getSauderWebsiteStaffDirectoryTasks('pending');
$content .= boxify($sauderWebsiteStaffDirectoryTasks, 'approve', 'SauderWebsiteStaffDirectory');

$content .= "<div class='bigicon sauderstaffphotodirectory'></div>";
$sauderStaffPhotoDirectoryTasks = $dataMgr->getSauderStaffPhotoDirectoryTasks('pending');
$content .= boxify($sauderStaffPhotoDirectoryTasks, 'approve', 'SauderStaffPhotoDirectory');

$content .= "<div class='bigicon doornameplate'></div>";
$doorNamePlateTasks = $dataMgr->getDoorNamePlateTasks('pending');
$content .= boxify($doorNamePlateTasks, 'approve', 'DoorNamePlate');

$content .= "<div class='bigicon sharedmailbox'></div>";
$sharedMailBoxAccessTasks= $dataMgr->getSharedMailBoxAccessTasks('pending');
$content .= boxify($sharedMailBoxAccessTasks, 'approve', 'SharedMailSlotAccessor');

$content .= "<div class='bigicon mailbox'></div>";
$mailBoxAccessTasks = $dataMgr->getMailBoxAccessTasks('pending');
$content .= boxify($mailBoxAccessTasks, 'approve', 'MailSlotAccessor');

$content .= "<div class='bigicon businesscard'></div>";
$businessCardTasks= $dataMgr->getBusinessCardTasks('pending');
$content .= boxify($businessCardTasks, 'approve', 'BusinessCard');

$content .= "<div class='bigicon attendancetracker'></div>";
$PATSystemAccountTasks = $dataMgr->getPATSystemAccountTasks('pending');
$content .= boxify($PATSystemAccountTasks, 'approve', 'PATSystemAccount');

$content .= "</td><td>";
$content .= "<h2>Done Tasks</h2>";

$content .= "<div class='bigicon tenure'></div>";
$generalInformationTasks = $dataMgr->getGeneralInformationTasks('done');
$content .= boxify($generalInformationTasks, 'undo', 'GeneralInformation');

$content .= "<div class='bigicon telephone'></div>";
$voiceServiceTasks = $dataMgr->getVoiceServiceTasks('done');
$content .= boxify($voiceServiceTasks, 'undo', 'VoiceService');

$content .= "<div class='bigicon internalphonelistregistry'></div>";
$internalPhoneListRegistryTasks = $dataMgr->getInternalPhoneListRegistryTasks('done');
$content .= boxify($internalPhoneListRegistryTasks, 'undo', 'GeneralInformation');

$content .= "<div class='bigicon ubconlinedirectory'></div>";
$UBConlineDirectoryTasks = $dataMgr->getUBConlineDirectoryTasks('done');
$content .= boxify($UBConlineDirectoryTasks, 'undo', 'UBConlineDirectory');

$content .= "<div class='bigicon sauderwebsitestaffdirectory'></div>";
$sauderWebsiteStaffDirectoryTasks = $dataMgr->getSauderWebsiteStaffDirectoryTasks('done');
$content .= boxify($sauderWebsiteStaffDirectoryTasks, 'undo', 'SauderWebsiteStaffDirectory');

$content .= "<div class='bigicon sauderstaffphotodirectory'></div>";
$sauderStaffPhotoDirectoryTasks = $dataMgr->getSauderStaffPhotoDirectoryTasks('done');
$content .= boxify($sauderStaffPhotoDirectoryTasks, 'undo', 'SauderStaffPhotoDirectory');

$content .= "<div class='bigicon doornameplate'></div>";
$doorNamePlateTasks = $dataMgr->getDoorNamePlateTasks('done');
$content .= boxify($doorNamePlateTasks, 'undo', 'DoorNamePlate');

$content .= "<div class='bigicon sharedmailbox'></div>";
$sharedMailBoxAccessTasks= $dataMgr->getSharedMailBoxAccessTasks('done');
$content .= boxify($sharedMailBoxAccessTasks, 'undo', 'SharedMailSlotAccessor');

$content .= "<div class='bigicon mailbox'></div>";
$mailBoxAccessTasks = $dataMgr->getMailBoxAccessTasks('done');
$content .= boxify($mailBoxAccessTasks, 'undo', 'MailSlotAccessor');

$content .= "<div class='bigicon businesscard'></div>";
$businessCardTasks= $dataMgr->getBusinessCardTasks('done');
$content .= boxify($businessCardTasks, 'undo', 'BusinessCard');

$content .= "<div class='bigicon attendancetracker'></div>";
$PATSystemAccountTasks = $dataMgr->getPATSystemAccountTasks('done');
$content .= boxify($PATSystemAccountTasks, 'undo', 'PATSystemAccount');

$content .= "</td></tr></table>";

?>

