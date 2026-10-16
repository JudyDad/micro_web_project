# P0.1 generated screen and menu matrix

Lexical enumeration of csproj Compile entries; comments and literals masked. Potential form reachability deliberately over-approximates execution; method calls, visibility, runtime branching and helper-mediated navigation need review. No desktop or network executed.

## Every compiled Form subclass

| Screen | Source | Potential incoming paths | Status |
|---|---|---|---|
| AboutUs_Form | MyWorkApplication/AboutUs_Form.cs:11 | MainForm.aboutHopeCenterToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:1055 | potential-static-path |
| AddNewAidSource | MyWorkApplication/AddNewAidSource.cs:13 | MainForm.aidToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:891; MainForm.aidSourcesToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:903 | potential-static-path |
| AddNewChecklistMember | MyWorkApplication/AddNewChecklistMember.cs:11 | CheckList_Form.Add_K_button_Click → MyWorkApplication/CheckList_Form.cs:1567; CheckList_Form.Add_T_button_Click → MyWorkApplication/CheckList_Form.cs:1585; CheckList_Form.Add_V_button_Click → MyWorkApplication/CheckList_Form.cs:1603; CheckList_Form.Add_C_button_Click → MyWorkApplication/CheckList_Form.cs:1621; MainForm.visitorsToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:1481; MainForm.keyPersonsToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:1494; MainForm.commityMemberToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:1507 | potential-static-path |
| AddNewChecklistPosition | MyWorkApplication/AddNewChecklistPosition.cs:10 | AddNewChecklistMember.AddPosition_button_Click → MyWorkApplication/AddNewChecklistMember.cs:264 | potential-static-path |
| AddNewDonor | MyWorkApplication/AddNewDonor.cs:13 | MainForm.donorToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:878 | potential-static-path |
| AddNewEducation | MyWorkApplication/AddNewEducation.cs:13 | Application_Form.NewEducation_button_Click → MyWorkApplication/Application_Form.cs:5250; MainForm.Education_ToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:639 | potential-static-path |
| AddNewExperience | MyWorkApplication/AddNewExperience.cs:13 | Application_Form.NewWork_button_Click → MyWorkApplication/Application_Form.cs:5271; MainForm.workExperience_ToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:652 | potential-static-path |
| AddNewGroup | MyWorkApplication/AddNewGroup.cs:14 | Loans_Form.AddGroup_button_Click → MyWorkApplication/Loans_Form.cs:1160 | potential-static-path |
| AddNewPriest | MyWorkApplication/AddNewPriest.cs:10 | Application_Form.AddPriest_button_Click → MyWorkApplication/Application_Form.cs:5292; MainForm.newPriest_ToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:915 | potential-static-path |
| AddNewStateReasons | MyWorkApplication/AddNewStateReasons.cs:13 | None | no-static-path-or-helper-mediated; review |
| AddNewStreet | MyWorkApplication/AddNewStreet.cs:13 | MainForm.editProjectStreetNamesToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:627 | potential-static-path |
| AdminPanel_From | MyWorkApplication/AdminPanel_From.cs:21 | MainForm.adminPageToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:600 | potential-static-path |
| Agriculture_Form | MyWorkApplication/Agriculture_Form.cs:12 | Application_Form.AddExtention_button_Click → MyWorkApplication/Application_Form.cs:6058; MainForm.rehabAppToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:940 | potential-static-path |
| ApplicationRehab_Form | MyWorkApplication/ApplicationRehab_Form.cs:12 | Application_Form.AddExtention_button_Click → MyWorkApplication/Application_Form.cs:6053 | no-static-path-or-helper-mediated; review |
| Application_Form | MyWorkApplication/Application_Form.cs:20 | Application_Form.Check_National_Number → MyWorkApplication/Application_Form.cs:2350 | no-static-path-or-helper-mediated; review |
| Application_MessageBox | MyWorkApplication/Application_MessageBox.cs:6 | Application_Form.Check_National_Number → MyWorkApplication/Application_Form.cs:2345; Application_New_Form.P_NationalNum_textBox_Leave → MyWorkApplication/Application_New_Form.cs:2411; Application_New_Form.FamilyNum_textBox1_Leave → MyWorkApplication/Application_New_Form.cs:3249; Application_New_Form.Save_Application → MyWorkApplication/Application_New_Form.cs:4384; Application_New_Form.Save_Application → MyWorkApplication/Application_New_Form.cs:4500; Application_New_Form.Save_Application → MyWorkApplication/Application_New_Form.cs:5600 | potential-static-path |
| Application_New_Form | MyWorkApplication/Application_New_Form.cs:23 | AdminPanel_From.MP_dataGridView_RowHeaderMouseDoubleClick → MyWorkApplication/AdminPanel_From.cs:1523; AdminPanel_From.MP_dataGridView_CellDoubleClick → MyWorkApplication/AdminPanel_From.cs:1565; AdminPanel_From.MP_dataGridView_CellDoubleClick → MyWorkApplication/AdminPanel_From.cs:1570; Application_New_Form.P_NationalNum_textBox_Leave → MyWorkApplication/Application_New_Form.cs:2415; Application_New_Form.FamilyNum_textBox1_Leave → MyWorkApplication/Application_New_Form.cs:3253; Application_New_Form.AddPartner_button_Click → MyWorkApplication/Application_New_Form.cs:3928; Application_New_Form.Save_Application → MyWorkApplication/Application_New_Form.cs:4388; Application_New_Form.Save_Application → MyWorkApplication/Application_New_Form.cs:4504; Application_New_Form.Save_Application → MyWorkApplication/Application_New_Form.cs:5604; MainForm.zeroToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:1455; MainForm.full_ToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:1468; Search_Form.OpenReportRecord → MyWorkApplication/Search_Form.cs:3322; SpecialCase_Form.application_toolStripMenuItem_Click → MyWorkApplication/SpecialCase_Form.cs:517; Notification_Box.applicationToolStripMenuItem_Click → MyWorkApplication/Notification_Box.cs:432; TaskBoard_Form.MP_dataGridView_RowHeaderMouseDoubleClick → MyWorkApplication/TaskBoard_Form.cs:1182; VOpening_Other_Form.application_toolStripMenuItem_Click → MyWorkApplication/Visit Forms/VOpening_Other_Form.cs:993; VClosing_Form.application_toolStripMenuItem_Click → MyWorkApplication/Visit Forms/VClosing_Form.cs:753; VOpening_Taxi_Form.application_toolStripMenuItem_Click → MyWorkApplication/Visit Forms/VOpening_Taxi_Form.cs:876; V_ME_Other_Form.application_toolStripMenuItem_Click → MyWorkApplication/Visit Forms/V_ME_Other_Form.cs:735; V_ME_Taxi_Form.application_toolStripMenuItem_Click → MyWorkApplication/Visit Forms/V_ME_Taxi_Form.cs:728 | potential-static-path |
| Attachments_Form | MyWorkApplication/Attachments_Form.cs:13 | Application_Form.toolStripMenuItem3_Click → MyWorkApplication/Application_Form.cs:5794; Application_New_Form.attachments_toolStripMenuItem3_Click → MyWorkApplication/Application_New_Form.cs:8102; CheckList_Form.attachment_toolStripMenuItem3_Click → MyWorkApplication/CheckList_Form.cs:1657; Loans_Form.attachments_toolStripMenuItem_Click → MyWorkApplication/Loans_Form.cs:1518; MainForm.newAttachmentToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:767; Search_Form.OpenReportRecord → MyWorkApplication/Search_Form.cs:3366 | potential-static-path |
| Chat_Form | MyWorkApplication/Chat_Form.cs:11 | MainForm.chatGroupToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:1042 | potential-static-path |
| CheckList_Form | MyWorkApplication/CheckList_Form.cs:24 | Application_Form.showChecklistToolStripMenuItem_Click → MyWorkApplication/Application_Form.cs:5799; Application_New_Form.showChecklistToolStripMenuItem_Click → MyWorkApplication/Application_New_Form.cs:8108; MainForm.newCheckListToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:613; Search_Form.OpenReportRecord → MyWorkApplication/Search_Form.cs:3431; Notification_Box.checklistToolStripMenuItem_Click → MyWorkApplication/Notification_Box.cs:452 | potential-static-path |
| Check_NationalNumber_Form | MyWorkApplication/Check_NationalNumber_Form.cs:11 | MainForm.checkIDNumberToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:1446 | potential-static-path |
| ChooseVisitKind_Form | MyWorkApplication/Visit Forms/ChooseVisitKind_Form.cs:10 | Application_Form.toolStripMenuItem1_Click → MyWorkApplication/Application_Form.cs:5782; Application_New_Form.visits_toolStripMenuItem1_Click → MyWorkApplication/Application_New_Form.cs:8090; CheckList_Form.visit_toolStripMenuItem1_Click → MyWorkApplication/CheckList_Form.cs:1641; MainForm.monitoringVisitsToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:676; Search_Form.OpenReportRecord → MyWorkApplication/Search_Form.cs:3383; Notification_Box.visitsToolStripMenuItem_Click → MyWorkApplication/Notification_Box.cs:492 | potential-static-path |
| Encrypt | MyWorkApplication/Encrypt.cs:6 | None | no-static-path-or-helper-mediated; review |
| ExecutiveFiles_Form | MyWorkApplication/ExecutiveFiles_Form.cs:11 | MainForm.newExecutiveFileToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:780 | potential-static-path |
| Help_Form | MyWorkApplication/Help_Form.cs:7 | MainForm.readTheManualToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:1213 | potential-static-path |
| Loans_Form | MyWorkApplication/Loans_Form.cs:18 | Application_Form.toolStripMenuItem2_Click → MyWorkApplication/Application_Form.cs:5788; Application_New_Form.payments_toolStripMenuItem2_Click → MyWorkApplication/Application_New_Form.cs:8096; CheckList_Form.loan_toolStripMenuItem2_Click → MyWorkApplication/CheckList_Form.cs:1649; MainForm.loansPaymentsToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:741; Payments_Form.dataGridView_RowHeaderMouseDoubleClick → MyWorkApplication/Payments_Form.cs:405; Notification_Box.loansToolStripMenuItem_Click → MyWorkApplication/Notification_Box.cs:472 | potential-static-path |
| Log_Form | MyWorkApplication/Log_Form.cs:12 | MainForm.logToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:1005 | potential-static-path |
| Login_Form | MyWorkApplication/Login_Form.cs:16 | MainForm.ProfilePicture_button_Click → MyWorkApplication/MainForm.cs:544; Program.Main → MyWorkApplication/Program.cs:243 | potential-static-path |
| MainForm | MyWorkApplication/MainForm.cs:16 | CityRecordWindow.Run → MyWorkApplication/Classes/CityRecordWindow.cs:114; Program.Main → MyWorkApplication/Program.cs:251 | potential-static-path |
| NewIdea_Form | MyWorkApplication/NewIdea_Form.cs:10 | Agriculture_Form.addNotesToolStripMenuItem_Click → MyWorkApplication/Agriculture_Form.cs:446; ApplicationRehab_Form.addNotesToolStripMenuItem_Click → MyWorkApplication/ApplicationRehab_Form.cs:655; Application_Form.addNotesToolStripMenuItem_Click → MyWorkApplication/Application_Form.cs:5835; Application_New_Form.addNotesToolStripMenuItem_Click → MyWorkApplication/Application_New_Form.cs:8144; Loans_Form.addNotesToolStripMenuItem_Click → MyWorkApplication/Loans_Form.cs:1554; MainForm.sendFeedbackToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:1200; SpecialCase_Form.addNotesToolStripMenuItem_Click → MyWorkApplication/SpecialCase_Form.cs:551; VOpening_Other_Form.addNotesToolStripMenuItem_Click → MyWorkApplication/Visit Forms/VOpening_Other_Form.cs:1029; VClosing_Form.addNotesToolStripMenuItem_Click → MyWorkApplication/Visit Forms/VClosing_Form.cs:789; VOpening_Taxi_Form.addNotesToolStripMenuItem_Click → MyWorkApplication/Visit Forms/VOpening_Taxi_Form.cs:912; V_ME_Other_Form.addNotesToolStripMenuItem_Click → MyWorkApplication/Visit Forms/V_ME_Other_Form.cs:771; V_ME_Taxi_Form.addNotesToolStripMenuItem_Click → MyWorkApplication/Visit Forms/V_ME_Taxi_Form.cs:764 | potential-static-path |
| Notification_Box | MyWorkApplication/Notification_Box.cs:15 | MainForm.Notifications_button_Click → MyWorkApplication/MainForm.cs:1337 | potential-static-path |
| Partners_MessageBox | MyWorkApplication/Partners_MessageBox.cs:7 | V_ME_Other_Form.Save_button_Click → MyWorkApplication/Visit Forms/V_ME_Other_Form.cs:358; V_ME_Taxi_Form.Save_button_Click → MyWorkApplication/Visit Forms/V_ME_Taxi_Form.cs:605 | potential-static-path |
| Payments_Form | MyWorkApplication/Payments_Form.cs:16 | MainForm.paymentsSchedualToolStripMenuItem_Click_1 → MyWorkApplication/MainForm.cs:754 | potential-static-path |
| ProfessionMeetings_Form | MyWorkApplication/ProfessionMeetings_Form.cs:13 | MainForm.professionMeetingsToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:728; Search_Form.OpenReportRecord → MyWorkApplication/Search_Form.cs:3466 | potential-static-path |
| Purchasing_Form | MyWorkApplication/Purchasing_Form.cs:17 | None | no-static-path-or-helper-mediated; review |
| QR_Form | MyWorkApplication/QR_Form.cs:8 | None | no-static-path-or-helper-mediated; review |
| Search_Form | MyWorkApplication/Search_Form.cs:34 | AdminPanel_From.searchToolStripMenuItem_Click → MyWorkApplication/AdminPanel_From.cs:2059; MainForm.searchToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:979; CityRecordWindow.Run → MyWorkApplication/Classes/CityRecordWindow.cs:119; Search_Form.searchToolStripMenuItem_Click → MyWorkApplication/Search_Form.cs:3757; TaskBoard_Form.searchToolStripMenuItem_Click → MyWorkApplication/TaskBoard_Form.cs:1315 | potential-static-path |
| SendMessage_Box | MyWorkApplication/SendMessage_Box.cs:10 | MainForm.sendAMessageToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:1068 | potential-static-path |
| ShowDetails_Form | MyWorkApplication/ShowDetails_Form.cs:12 | AdminPanel_From.MP_dataGridView_CellContentClick → MyWorkApplication/AdminPanel_From.cs:1462; Search_Form.MP_dataGridView_CellContentClick → MyWorkApplication/Search_Form.cs:3215; Search_Form.OpenReportRecord → MyWorkApplication/Search_Form.cs:3341; Statistics_Form.showDetailsToolStripMenuItem_Click → MyWorkApplication/Statistics_Form.cs:1529 | potential-static-path |
| SpecialCase_Form | MyWorkApplication/SpecialCase_Form.cs:12 | MainForm.specialCaseToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:715; Search_Form.OpenReportRecord → MyWorkApplication/Search_Form.cs:3449 | potential-static-path |
| Statistics_Form | MyWorkApplication/Statistics_Form.cs:36 | AdminPanel_From.statisticsToolStripMenuItem_Click → MyWorkApplication/AdminPanel_From.cs:2071; MainForm.statisticsToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:953; Search_Form.statisticsToolStripMenuItem_Click → MyWorkApplication/Search_Form.cs:3770; TaskBoard_Form.statisticsToolStripMenuItem_Click → MyWorkApplication/TaskBoard_Form.cs:1327 | potential-static-path |
| TaskBoard_Form | MyWorkApplication/TaskBoard_Form.cs:18 | AdminPanel_From.taskBoardToolStripMenuItem_Click → MyWorkApplication/AdminPanel_From.cs:2083; MainForm.taskBoardToolStripMenuItem1_Click → MyWorkApplication/MainForm.cs:966; Search_Form.taskBoardToolStripMenuItem_Click → MyWorkApplication/Search_Form.cs:3783; TaskBoard_Form.taskBoardToolStripMenuItem_Click → MyWorkApplication/TaskBoard_Form.cs:1341 | potential-static-path |
| Timeline_Form | MyWorkApplication/Timeline_Form.cs:14 | MainForm.projectLifeCycleToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:992 | potential-static-path |
| User_Form | MyWorkApplication/User_Form.cs:15 | MainForm.usersToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:1081 | potential-static-path |
| VClosing_Form | MyWorkApplication/Visit Forms/VClosing_Form.cs:12 | ChooseVisitKind_Form.VClosing_button_Click → MyWorkApplication/Visit Forms/ChooseVisitKind_Form.cs:329; ChooseVisitKind_Form.VClosing_button_Click → MyWorkApplication/Visit Forms/ChooseVisitKind_Form.cs:334; ChooseVisitKind_Form.dataGridView2_CellDoubleClick → MyWorkApplication/Visit Forms/ChooseVisitKind_Form.cs:401; ChooseVisitKind_Form.dataGridView2_CellDoubleClick → MyWorkApplication/Visit Forms/ChooseVisitKind_Form.cs:406 | potential-static-path |
| VClosing_Rehab_Form | MyWorkApplication/Visit Forms/VClosing_Rehab_Form.cs:11 | ChooseVisitKind_Form.VClosing_button_Click → MyWorkApplication/Visit Forms/ChooseVisitKind_Form.cs:324; ChooseVisitKind_Form.dataGridView2_CellDoubleClick → MyWorkApplication/Visit Forms/ChooseVisitKind_Form.cs:396 | potential-static-path |
| VOpening_Other_Form | MyWorkApplication/Visit Forms/VOpening_Other_Form.cs:13 | ChooseVisitKind_Form.VOpening_button_Click → MyWorkApplication/Visit Forms/ChooseVisitKind_Form.cs:254; ChooseVisitKind_Form.dataGridView1_CellDoubleClick → MyWorkApplication/Visit Forms/ChooseVisitKind_Form.cs:370 | potential-static-path |
| VOpening_Taxi_Form | MyWorkApplication/Visit Forms/VOpening_Taxi_Form.cs:13 | ChooseVisitKind_Form.VOpening_button_Click → MyWorkApplication/Visit Forms/ChooseVisitKind_Form.cs:249; ChooseVisitKind_Form.dataGridView1_CellDoubleClick → MyWorkApplication/Visit Forms/ChooseVisitKind_Form.cs:365 | potential-static-path |
| V_ME_Other_Form | MyWorkApplication/Visit Forms/V_ME_Other_Form.cs:13 | Partners_MessageBox.New_button_Click → MyWorkApplication/Partners_MessageBox.cs:66; Search_Form.OpenReportRecord → MyWorkApplication/Search_Form.cs:3412; ChooseVisitKind_Form.VMonitoring_button_Click → MyWorkApplication/Visit Forms/ChooseVisitKind_Form.cs:301; ChooseVisitKind_Form.dataGridView_CellDoubleClick → MyWorkApplication/Visit Forms/ChooseVisitKind_Form.cs:439 | potential-static-path |
| V_ME_Taxi_Form | MyWorkApplication/Visit Forms/V_ME_Taxi_Form.cs:13 | Partners_MessageBox.New_button_Click → MyWorkApplication/Partners_MessageBox.cs:61; Search_Form.OpenReportRecord → MyWorkApplication/Search_Form.cs:3407; ChooseVisitKind_Form.VMonitoring_button_Click → MyWorkApplication/Visit Forms/ChooseVisitKind_Form.cs:295; ChooseVisitKind_Form.dataGridView_CellDoubleClick → MyWorkApplication/Visit Forms/ChooseVisitKind_Form.cs:434 | potential-static-path |
| Visit_Results_Form | MyWorkApplication/Visit Forms/Visit_Results_Form.cs:18 | MainForm.monitorVisitResultsToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:689 | potential-static-path |
| Visits_Schedule_Form | MyWorkApplication/Visit Forms/Visits_Schedule_Form.cs:19 | MainForm.visitDatesSchedualToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:702 | potential-static-path |
| Visits_Statistics | MyWorkApplication/Visit Forms/Visits_Statistics.cs:13 | MainForm.visitorsStatisticsToolStripMenuItem_Click → MyWorkApplication/MainForm.cs:927 | potential-static-path |

## Forms excluded from project compilation

| Screen | Source | Status |
|---|---|---|
| Encrypt_Decrypt_Form | MyWorkApplication/Encrypt_Decrypt_Form.cs:15 | not in csproj Compile; not active build |
| ProjectLifeCycle | MyWorkApplication/ProjectLifeCycle.cs:10 | not in csproj Compile; not active build |
| WebSection | MyWorkApplication/WebSection.cs:9 | not in csproj Compile; not active build |

## Every MainForm menu item

| Menu identifier / attachment | Definition | Handler | Form targets / disposition |
|---|---|---|---|
| applicationToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:52 | None | container-or-unbound |
| NewApplicationToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:53 | None | container-or-unbound |
| adminPageToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:54 | adminPageToolStripMenuItem_Click | AdminPanel_From at MyWorkApplication/MainForm.cs:600 |
| aDMINToolsToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:55 | None | container-or-unbound |
| editProjectStreetNamesمناطقالسكنوالعملToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:56 | editProjectStreetNamesToolStripMenuItem_Click | AddNewStreet at MyWorkApplication/MainForm.cs:627 |
| تعديلالتحصيلالعلميEducationToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:57 | Education_ToolStripMenuItem_Click | AddNewEducation at MyWorkApplication/MainForm.cs:639 |
| workExperienceتعديلالعملوالخبراتالسابقةToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:58 | workExperience_ToolStripMenuItem_Click | AddNewExperience at MyWorkApplication/MainForm.cs:652 |
| donorToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:59 | donorToolStripMenuItem_Click | AddNewDonor at MyWorkApplication/MainForm.cs:878 |
| aidSourcesToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:60 | aidSourcesToolStripMenuItem_Click | AddNewAidSource at MyWorkApplication/MainForm.cs:903 |
| newCheckListToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:61 | newCheckListToolStripMenuItem_Click | CheckList_Form at MyWorkApplication/MainForm.cs:613 |
| newZeroApplicationToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:62 | None | container-or-unbound |
| newNewApplicationToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:63 | None | container-or-unbound |
| financialToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:64 | None | container-or-unbound |
| loansPaymentsToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:65 | loansPaymentsToolStripMenuItem_Click | Loans_Form at MyWorkApplication/MainForm.cs:741 |
| paymentsSchedualToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:66 | paymentsSchedualToolStripMenuItem_Click_1 | Payments_Form at MyWorkApplication/MainForm.cs:754 |
| newAttachmentToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:67 | newAttachmentToolStripMenuItem_Click | Attachments_Form at MyWorkApplication/MainForm.cs:767 |
| newExecutiveFileToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:68 | newExecutiveFileToolStripMenuItem_Click | ExecutiveFiles_Form at MyWorkApplication/MainForm.cs:780 |
| mEToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:69 | None | container-or-unbound |
| monitoringVisitsToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:70 | monitoringVisitsToolStripMenuItem_Click | ChooseVisitKind_Form at MyWorkApplication/MainForm.cs:676 |
| visitDatesSchedualToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:71 | visitDatesSchedualToolStripMenuItem_Click | Visits_Schedule_Form at MyWorkApplication/MainForm.cs:702 |
| monitorVisitResultsToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:72 | monitorVisitResultsToolStripMenuItem_Click | Visit_Results_Form at MyWorkApplication/MainForm.cs:689 |
| visitorsStatisticsToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:73 | visitorsStatisticsToolStripMenuItem_Click | Visits_Statistics at MyWorkApplication/MainForm.cs:927 |
| specialCaseToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:74 | specialCaseToolStripMenuItem_Click | SpecialCase_Form at MyWorkApplication/MainForm.cs:715 |
| toolsToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:75 | None | container-or-unbound |
| searchToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:76 | searchToolStripMenuItem_Click | Search_Form at MyWorkApplication/MainForm.cs:979 |
| statisticsToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:77 | statisticsToolStripMenuItem_Click | Statistics_Form at MyWorkApplication/MainForm.cs:953 |
| taskBoardToolStripMenuItem1 (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:78 | taskBoardToolStripMenuItem1_Click | TaskBoard_Form at MyWorkApplication/MainForm.cs:966 |
| logToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:79 | logToolStripMenuItem_Click | Log_Form at MyWorkApplication/MainForm.cs:1005 |
| themeToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:80 | None | container-or-unbound |
| darkToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:81 | darkToolStripMenuItem_Click_1 | bound-action |
| lightToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:82 | lightToolStripMenuItem_Click_1 | bound-action |
| sendAMessageToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:83 | sendAMessageToolStripMenuItem_Click | SendMessage_Box at MyWorkApplication/MainForm.cs:1068 |
| dataBackupRestoreToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:84 | None | container-or-unbound |
| backupToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:85 | backupToolStripMenuItem_Click | bound-action |
| restoreFromFileToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:86 | restoreFromFileToolStripMenuItem_Click | bound-action |
| usersToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:87 | usersToolStripMenuItem_Click | User_Form at MyWorkApplication/MainForm.cs:1081 |
| checkIDNumberToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:88 | checkIDNumberToolStripMenuItem_Click | Check_NationalNumber_Form at MyWorkApplication/MainForm.cs:1446 |
| aboutUsToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:89 | None | container-or-unbound |
| aboutHopeCenterToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:90 | aboutHopeCenterToolStripMenuItem_Click | AboutUs_Form at MyWorkApplication/MainForm.cs:1055 |
| sendFeedbackToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:91 | sendFeedbackToolStripMenuItem_Click | NewIdea_Form at MyWorkApplication/MainForm.cs:1200 |
| readTheManualToolStripMenuItem (detached legacy menu) | MyWorkApplication/MainForm.Designer.cs:92 | readTheManualToolStripMenuItem_Click | Help_Form at MyWorkApplication/MainForm.cs:1213 |
| applicationToolStripMenuItem1 (attached) | MyWorkApplication/MainForm.Designer.cs:97 | None | container-or-unbound |
| newApp_ToolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:98 | None | container-or-unbound |
| zero_ToolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:99 | zeroToolStripMenuItem_Click | Application_New_Form at MyWorkApplication/MainForm.cs:1455 |
| full_ToolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:100 | full_ToolStripMenuItem_Click | Application_New_Form at MyWorkApplication/MainForm.cs:1468 |
| Checklist_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:101 | newCheckListToolStripMenuItem_Click | CheckList_Form at MyWorkApplication/MainForm.cs:613 |
| AdminPanel_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:102 | adminPageToolStripMenuItem_Click | AdminPanel_From at MyWorkApplication/MainForm.cs:600 |
| toolStripMenuItem4 (attached) | MyWorkApplication/MainForm.Designer.cs:103 | None | container-or-unbound |
| newStreet_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:104 | editProjectStreetNamesToolStripMenuItem_Click | AddNewStreet at MyWorkApplication/MainForm.cs:627 |
| newEdu_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:105 | Education_ToolStripMenuItem_Click | AddNewEducation at MyWorkApplication/MainForm.cs:639 |
| newWork_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:106 | workExperience_ToolStripMenuItem_Click | AddNewExperience at MyWorkApplication/MainForm.cs:652 |
| newDonor_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:107 | donorToolStripMenuItem_Click | AddNewDonor at MyWorkApplication/MainForm.cs:878 |
| newAid_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:108 | aidToolStripMenuItem_Click | AddNewAidSource at MyWorkApplication/MainForm.cs:891 |
| newAidSource_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:109 | aidSourcesToolStripMenuItem_Click | AddNewAidSource at MyWorkApplication/MainForm.cs:903 |
| newPriest_ToolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:110 | newPriest_ToolStripMenuItem_Click | AddNewPriest at MyWorkApplication/MainForm.cs:915 |
| checklistMembersToolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:111 | None | container-or-unbound |
| visitorsToolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:112 | visitorsToolStripMenuItem_Click | AddNewChecklistMember at MyWorkApplication/MainForm.cs:1481 |
| keyPersonsToolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:113 | keyPersonsToolStripMenuItem_Click | AddNewChecklistMember at MyWorkApplication/MainForm.cs:1494 |
| commityMemberToolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:114 | commityMemberToolStripMenuItem_Click | AddNewChecklistMember at MyWorkApplication/MainForm.cs:1507 |
| financialToolStripMenuItem1 (attached) | MyWorkApplication/MainForm.Designer.cs:115 | None | container-or-unbound |
| Loan_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:116 | loansPaymentsToolStripMenuItem_Click | Loans_Form at MyWorkApplication/MainForm.cs:741 |
| Payments_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:117 | paymentsSchedualToolStripMenuItem_Click_1 | Payments_Form at MyWorkApplication/MainForm.cs:754 |
| Attachments_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:118 | newAttachmentToolStripMenuItem_Click | Attachments_Form at MyWorkApplication/MainForm.cs:767 |
| purchasingToolStripMenuItem (attached / hidden) | MyWorkApplication/MainForm.Designer.cs:119 | purchasingToolStripMenuItem_Click | bound-action |
| mEToolStripMenuItem1 (attached) | MyWorkApplication/MainForm.Designer.cs:120 | None | container-or-unbound |
| Visit_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:121 | monitoringVisitsToolStripMenuItem_Click | ChooseVisitKind_Form at MyWorkApplication/MainForm.cs:676 |
| VDates_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:122 | visitDatesSchedualToolStripMenuItem_Click | Visits_Schedule_Form at MyWorkApplication/MainForm.cs:702 |
| VResults_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:123 | monitorVisitResultsToolStripMenuItem_Click | Visit_Results_Form at MyWorkApplication/MainForm.cs:689 |
| VStatistics_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:124 | visitorsStatisticsToolStripMenuItem_Click | Visits_Statistics at MyWorkApplication/MainForm.cs:927 |
| SpecialCase_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:125 | specialCaseToolStripMenuItem_Click | SpecialCase_Form at MyWorkApplication/MainForm.cs:715 |
| ProfessionMeetings_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:126 | professionMeetingsToolStripMenuItem_Click | ProfessionMeetings_Form at MyWorkApplication/MainForm.cs:728 |
| toolsToolStripMenuItem1 (attached) | MyWorkApplication/MainForm.Designer.cs:127 | None | container-or-unbound |
| Search_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:128 | searchToolStripMenuItem_Click | Search_Form at MyWorkApplication/MainForm.cs:979 |
| Statistics_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:129 | statisticsToolStripMenuItem_Click | Statistics_Form at MyWorkApplication/MainForm.cs:953 |
| Taskboard_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:130 | taskBoardToolStripMenuItem1_Click | TaskBoard_Form at MyWorkApplication/MainForm.cs:966 |
| Log_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:131 | logToolStripMenuItem_Click | Log_Form at MyWorkApplication/MainForm.cs:1005 |
| toolStripMenuItem27 (attached) | MyWorkApplication/MainForm.Designer.cs:132 | None | container-or-unbound |
| Dark_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:133 | darkToolStripMenuItem_Click_1 | bound-action |
| Light_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:134 | lightToolStripMenuItem_Click_1 | bound-action |
| Message_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:135 | sendAMessageToolStripMenuItem_Click | SendMessage_Box at MyWorkApplication/MainForm.cs:1068 |
| toolStripMenuItem31 (attached) | MyWorkApplication/MainForm.Designer.cs:136 | None | container-or-unbound |
| Backup_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:137 | backupToolStripMenuItem_Click | bound-action |
| Restore_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:138 | restoreFromFileToolStripMenuItem_Click | bound-action |
| Users_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:139 | usersToolStripMenuItem_Click | User_Form at MyWorkApplication/MainForm.cs:1081 |
| NationalNum_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:140 | checkIDNumberToolStripMenuItem_Click | Check_NationalNumber_Form at MyWorkApplication/MainForm.cs:1446 |
| helpToolStripMenuItem1 (attached) | MyWorkApplication/MainForm.Designer.cs:141 | None | container-or-unbound |
| AboutUs_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:142 | aboutHopeCenterToolStripMenuItem_Click | AboutUs_Form at MyWorkApplication/MainForm.cs:1055 |
| FeedBack_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:143 | sendFeedbackToolStripMenuItem_Click | NewIdea_Form at MyWorkApplication/MainForm.cs:1200 |
| Manual_toolStripMenuItem (attached) | MyWorkApplication/MainForm.Designer.cs:144 | readTheManualToolStripMenuItem_Click | Help_Form at MyWorkApplication/MainForm.cs:1213 |

## Print/export control bindings (including nonmatching handler names)

| Owner/control | Event/handler | Evidence |
|---|---|---|
| AddNewAidSource.ExportToExcel_button | Click / ExportToExcel_button_Click | MyWorkApplication/AddNewAidSource.Designer.cs:218 |
| AddNewDonor.ExportToExcel_button | Click / ExportToExcel_button_Click | MyWorkApplication/AddNewDonor.Designer.cs:219 |
| AddNewEducation.ExportToExcel_button | Click / ExportToExcel_button_Click | MyWorkApplication/AddNewEducation.Designer.cs:273 |
| AddNewExperience.ExportToExcel_button | Click / ExportToExcel_button_Click | MyWorkApplication/AddNewExperience.Designer.cs:308 |
| AddNewGroup.ExportToExcel_button | Click / ExportToExcel_button_Click | MyWorkApplication/AddNewGroup.Designer.cs:338 |
| AddNewStateReasons.ExportToExcel_button | Click / ExportToExcel_button_Click | MyWorkApplication/AddNewStateReasons.Designer.cs:219 |
| AddNewStreet.ExportToExcel_button | Click / ExportToExcel_button_Click | MyWorkApplication/AddNewStreet.Designer.cs:204 |
| AdminPanel_From.ExportToExcel_button | Click / ExportToExcel_button_Click | MyWorkApplication/AdminPanel_From.Designer.cs:1493 |
| CheckList_Form.Print_button | Click / button1_Click | MyWorkApplication/CheckList_Form.Designer.cs:373 |
| Log_Form.ExportToExcel_button | Click / ExportToExcel_button_Click | MyWorkApplication/Log_Form.Designer.cs:170 |
| Payments_Form.ExportToExcel_button | Click / ExportToExcel_button_Click | MyWorkApplication/Payments_Form.Designer.cs:139 |
| Search_Form.ExportToExcel_button | Click / ExportToExcel_button_Click | MyWorkApplication/Search_Form.Designer.cs:440 |
| Search_Form.export_ToolStripMenuItem | Click / ExportToExcel_button_Click | MyWorkApplication/Search_Form.Designer.cs:459 |
| Search_Form.export_applications_ToolStripMenuItem | Click / export_applications_ToolStripMenuItem_Click | MyWorkApplication/Search_Form.Designer.cs:466 |
| Notification_Box.ExportToExcel_button | Click / ExportToExcel_button_Click | MyWorkApplication/Notification_Box.Designer.cs:216 |
| TaskBoard_Form.ExportToExcel_button | Click / ExportToExcel_button_Click | MyWorkApplication/TaskBoard_Form.Designer.cs:328 |
| ShowDetails_Form.ExportToExcel_button | Click / ExportToExcel_button_Click | MyWorkApplication/ShowDetails_Form.Designer.cs:175 |
| Statistics_Form.ExportToExcel_button | Click / ExportToExcel_button_Click | MyWorkApplication/Statistics_Form.Designer.cs:555 |
| Visit_Results_Form.ExportToExcel_button | Click / ExportToExcel_button_Click | MyWorkApplication/Visit Forms/Visit_Results_Form.Designer.cs:200 |
| Visits_Statistics.ExportToExcel_button | Click / Export_Exel_Click | MyWorkApplication/Visit Forms/Visits_Statistics.Designer.cs:165 |
| Visits_Schedule_Form.ExportToExcel_button | Click / ExportToExcel_button_Click | MyWorkApplication/Visit Forms/Visits_Schedule_Form.Designer.cs:153 |

## Report, print and export entry methods

| Owner | Method | Evidence |
|---|---|---|
| AddNewAidSource | ExportToExcel_button_Click | MyWorkApplication/AddNewAidSource.cs:289 |
| AddNewDonor | ExportToExcel_button_Click | MyWorkApplication/AddNewDonor.cs:292 |
| AddNewEducation | ExportToExcel_button_Click | MyWorkApplication/AddNewEducation.cs:302 |
| AddNewExperience | ExportToExcel_button_Click | MyWorkApplication/AddNewExperience.cs:267 |
| AddNewGroup | ExportToExcel_button_Click | MyWorkApplication/AddNewGroup.cs:318 |
| AddNewStateReasons | ExportToExcel_button_Click | MyWorkApplication/AddNewStateReasons.cs:265 |
| AddNewStreet | ExportToExcel_button_Click | MyWorkApplication/AddNewStreet.cs:271 |
| AdminPanel_From | ExportToExcel_button_Click | MyWorkApplication/AdminPanel_From.cs:1079 |
| CheckList | Print_Checklist_Procedure | MyWorkApplication/Classes/CheckList.cs:401 |
| Log_Form | ExportToExcel_button_Click | MyWorkApplication/Log_Form.cs:305 |
| Payments_Form | ExportToExcel_button_Click | MyWorkApplication/Payments_Form.cs:225 |
| CityReportResult | ExportApplicationsAsync | MyWorkApplication/Classes/CityReportingService.cs:117 |
| Search_Form | ExportSelectedCityApplicationsAsync | MyWorkApplication/Search_Form.Reporting.cs:294 |
| Search_Form | Calculate_ImportExportMoney | MyWorkApplication/Search_Form.cs:2650 |
| Search_Form | ExportToExcel_button_Click | MyWorkApplication/Search_Form.cs:3063 |
| Search_Form | export_applications_ToolStripMenuItem_Click | MyWorkApplication/Search_Form.cs:3083 |
| Notification_Box | ExportToExcel_button_Click | MyWorkApplication/Notification_Box.cs:504 |
| TaskBoard_Form | ExportToExcel_button_Click | MyWorkApplication/TaskBoard_Form.cs:672 |
| ShowDetails_Form | ExportToExcel_button_Click | MyWorkApplication/ShowDetails_Form.cs:215 |
| Statistics_Form | ExportToExcel_button_Click | MyWorkApplication/Statistics_Form.cs:558 |
| Visit_Results_Form | ExportToExcel_button_Click | MyWorkApplication/Visit Forms/Visit_Results_Form.cs:606 |
| Visits_Statistics | Export_Exel_Click | MyWorkApplication/Visit Forms/Visits_Statistics.cs:260 |
| Visits_Schedule_Form | ExportToExcel_button_Click | MyWorkApplication/Visit Forms/Visits_Schedule_Form.cs:467 |
