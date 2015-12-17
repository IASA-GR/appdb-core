<table cellpadding="0" cellspacing="0">
    <tr>
        <td>
            <div class="docktop header">
            <?php $this->partial('header'); ?>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="listContainer">
            <div >
                <table style="table-layout: fixed" cellSpacing="0" cellPadding="0">
                    <tbody>
                        <tr >
                            <td>
                                <?php
                                        $vname = $this->getViewName();
                                        if(!isset($vname)){
                                            GadgetRequest::GetRequest()->ViewName = "simplelist";
                                            $vname = "simplelist";
                                        }
                                         $this->partial($vname);
                                    ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div></div>
        </div>
        </td>
    </tr>
    <tr>
        <td class="helpContainer" style="background-color: white;">
            <div id="helpContent"  style="width:100%;display:none;border:1px ridge #7D99AB;">
                <table  style="table-layout:fixed;width:100%">
                    <thead id="helpHeader" style="background-color: #f1efef">
                        <tr align="left">
                            <td valign="bottom" style="text-align: left;vertical-align: middle;">
                                <table style="border-style:none;text-decoration: none;" border="0">
                                    <tr valign="bottom">
                                        <td valign="bottom">
                                            <a class="backButton" style="text-decoration: none;border-style:none;cursor:pointer;"  onclick="gadgets.appdb.applications.hideHelp();" title="back">
                                                <img src="/gadgets/resources/skins/default/images/go_back.png" border="0" width="25px" height="25px" alt="back"/>
                                            </a>
                                        </td>
                                        <td valign="middle">
                                            <span class="fielvalue" style="padding:5px;">
                                                <a id="helpAboutButton"class="helpButton selectedButton" href="#" onclick="gadgets.appdb.applications.helpRegister(this);" title="register new software" ><span>New software</span></a>
                                            </span>
                                        </td>
                                        <td valign="middle">
                                            <span class="fielvalue" style="padding:5px;">
                                                <a class="helpButton" href="#" onclick="gadgets.appdb.applications.helpAbout(this);" title="about the application" ><span>About</span></a>
                                            </span>
                                        </td>
                                        <td valign="middle">
                                             <span class="fieldvalue" style="padding:5px;">
                                                <a class="helpButton" href="#" onclick="gadgets.appdb.applications.helpContact(this);" title="contact information"><span>Contact</span></a>
                                            </span>
                                        </td>
                                        <td valign="middle">
                                            <span class="fieldvalue" style="padding:5px;">
                                                <a class="helpButton" href="#" onclick="gadgets.appdb.applications.helpChangelog(this);" title="changelog"><span>Changelog</span></a>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="background-color: lightgray;height:2px;"></td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr >
                            <td >
                                <div id="helpDocs">
                                <div id="helpRegister">
                                    <b>Register new application</b>
                                    <p>Registration of a new application is possible only through the
                                    <a class="linkButton" href="http://appdb.egi.eu" target="_blank">AppDB</a> portal. In order this to be realized the user
                                    should hold/obtain an <a class="linkButton" href="https://www.egi.eu/sso" target=" _blank">EGI SSO account</a>.</p>
                                </div>
                                <div id="helpAbout" style="display:none">
                                    <b>About the gadget</b>
                                    <p style="padding-right:10px;">The <a class="linkButton" href="http://appdb.egi.eu/gadgets/editor" target="_blank">AppDB Web Gadget</a> is freely
                                    offered to the communities, institutions or even individual scientists
                                    and provides data visualization for the  <a class="linkButton" href="http://appdb.egi.eu/" target="_blank">AppDB</a>
                                    <a class="linkButton" href="https://wiki.egi.eu/wiki/TNA3.4_Technical_Services#Applications_Database" target="_blank"> web
                                    API</a> result sets, paging capabilities and user defined search operations. The
                                    gadget is constructed in such a way, as to provide high usability to
                                    external web portals and can be configured to display specific
                                    information from the applications database, without any change to the
                                    structure of the host site.</p>
                                    <p><b>About the AppDB</b></p>
                                    <p style="padding-right:10px;">The <a class="linkButton" href="http://appdb.egi.eu/" target="_blank">EGI Applications Database</a> stores information
                                    about tailor-made computing tools for scientists to use, and about the
                                    programmers and scientists who developed them. The software
									filed in AppDB are finished products, ready to be used on the
                                    European Distributed Computing Infrastructure (DCI). Storing pre-made
                                    software means that scientists don't have to spend research time
                                    developing their own software. Thus, by storing pre-made software
                                    and tools, AppDB aims to avoid duplication of effort across the DCI user
                                    communities, and to inspire scientists less familiar with programming
                                    into using the European Distributed Computing Infrastructure.
                                    </p>
                                </div>
                                <div id="helpContact"style="display:none">
                                    <b>Contact details</b>
                                    <p>Your feedback is valuable in the continuing development of the AppDB
                                        Gadget utility as well as the AppDB service, so please take note that we
                                        always welcome requests for new features through the
                                        <a class="linkButton" href="http://rt.egi.eu" target="_blank">EGI RT</a>  system (<a class="linkButton" href="https://wiki.egi.eu/wiki/New_Requirement_Manual" target="_blank">details</a>), as well as bug
                                        reports through the <a class="linkButton" href="http://helpdesk.egi.eu/" target="_blank">EGI Helpdesk</a>.</p>
                                </div>
                                <div id="helpChangelog" style="display:none;">
                                    <b>Changelog</b>
									<br/>
									<ul class="changelogItem">
                                        <li>
                                            <div class="changelogItem">
												0.1.4 (2012-03-12)
                                                <ul>
                                                    <li>Added categories list in search section </li>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                    <br/>
									<ul class="changelogItem">
                                        <li>
                                            <div class="changelogItem">
												0.1.3 (2012-03-12)
                                                <ul>
                                                    <li>Added tag list in search section (relates to <a target="_blank" href="https://rt.egi.eu/guest/Ticket/Display.html?id=3536" >EGI RT #3536</a>) </li>
                                                    <li>Added autocompletion lists in search section</li>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                    <br />
                                    <ul class="changelogItem">
                                        <li>
                                            <div class="changelogItem">
                                                0.1.2 (2011-03-29)
                                                <ul>
                                                    <li>Vo and country fields can also accept as a value the name instead of the id, in the gadget url</li>
                                                    <li>Removed region search option</li>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                    <br />
                                    <ul  class="changelogItem">
                                        <li>
                                            <div class="changelogItem">
                                                0.1.1 (2011-03-24)
                                                <ul>
                                                    <li>Filtering criteria are sorted in alphabetical order (<a target="_blank" href="https://rt.egi.eu/guest/Ticket/Display.html?id=1577">EGI RT #1577</a>)</li>
                                                    <li>Fixed a bug with double country flags in the software details view</li>
                                                    <li>Fixed a bug with the title alignment.</li>
                                                    <li>Fixed a bug in the iframe url which prevented paging of the resulting list.</li>
                                                    <li>Added changelog view</li>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                    <br/>
                                    <ul  class="changelogItem">
                                        <li>
                                            <div class="changelogItem">
                                                0.1.0 (2011-03-22)
                                                <ul>
                                                    <li>Initial public release</li>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                    
                                </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="dockbottom" >
            <table class="footer" cellSpacing="0" cellPadding="0" width="100%">
                <tbody>
                    <tr>
                        <td valign="bottom" >
                        <?php $this->partial('pager'); ?>
                            <div class="signature" style="text-align: center;color:#676767;">
                                   <a target="_blank" href="http://www.iasa.gr/" class="hiddentext" style="text-decoration: none;font-size: smaller;color:#676767;">
								   <font>&copy; Institute of Accelerating Systems and Applications, 2009-<?php echo date("Y");?>, Athens, Greece</font>
                                   </a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">
                           
                        </td>
                    </tr>
                </tbody>
            </table>
            </div>
        </td>
    </tr>
</table>






