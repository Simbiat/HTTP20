<?php
declare(strict_types=1);
namespace http20;

class Sharing
{
    #Linkage of extensions to MIME types
    public const extToMime = [
        '123' => 'application/vnd.lotus-1-2-3',
        '3dml' => 'text/vnd.in3d.3dml',
        '3g2' => 'video/3gpp2',
        '3gp' => 'video/3gpp',
        '7z' => 'application/x-7z-compressed',
        'aab' => 'application/x-authorware-bin',
        'aac' => 'audio/x-aac',
        'aam' => 'application/x-authorware-map',
        'aas' => 'application/x-authorware-seg',
        'abw' => 'application/x-abiword',
        'ac' => 'application/pkix-attr-cert',
        'acc' => 'application/vnd.americandynamics.acc',
        'ace' => 'application/x-ace-compressed',
        'acu' => 'application/vnd.acucobol',
        'adp' => 'audio/adpcm',
        'aep' => 'application/vnd.audiograph',
        'afp' => 'application/vnd.ibm.modcap',
        'ahead' => 'application/vnd.ahead.space',
        'ai' => 'application/postscript',
        'aif' => 'audio/x-aiff',
        'air' => 'application/vnd.adobe.air-application-installer-package+zip',
        'ait' => 'application/vnd.dvb.ait',
        'ami' => 'application/vnd.amiga.ami',
        'apk' => 'application/vnd.android.package-archive',
        'application' => 'application/x-ms-application',
        'apr' => 'application/vnd.lotus-approach',
        'asf' => 'video/x-ms-asf',
        'aso' => 'application/vnd.accpac.simply.aso',
        'atc' => 'application/vnd.acucorp',
        'atom' => 'application/atom+xml',
        'atomcat' => 'application/atomcat+xml',
        'atomsvc' => 'application/atomsvc+xml',
        'atx' => 'application/vnd.antix.game-component',
        'au' => 'audio/basic',
        'avi' => 'video/x-msvideo',
        'aw' => 'application/applixware',
        'azf' => 'application/vnd.airzip.filesecure.azf',
        'azs' => 'application/vnd.airzip.filesecure.azs',
        'azw' => 'application/vnd.amazon.ebook',
        'bcpio' => 'application/x-bcpio',
        'bdf' => 'application/x-font-bdf',
        'bdm' => 'application/vnd.syncml.dm+wbxml',
        'bed' => 'application/vnd.realvnc.bed',
        'bh2' => 'application/vnd.fujitsu.oasysprs',
        'bin' => 'application/octet-stream',
        'bmi' => 'application/vnd.bmi',
        'bmp' => 'image/bmp',
        'box' => 'application/vnd.previewsystems.box',
        'btif' => 'image/prs.btif',
        'bz' => 'application/x-bzip',
        'bz2' => 'application/x-bzip2',
        'c' => 'text/x-c',
        'c11amc' => 'application/vnd.cluetrust.cartomobile-config',
        'c11amz' => 'application/vnd.cluetrust.cartomobile-config-pkg',
        'c4g' => 'application/vnd.clonk.c4group',
        'cab' => 'application/vnd.ms-cab-compressed',
        'car' => 'application/vnd.curl.car',
        'cat' => 'application/vnd.ms-pki.seccat',
        'ccxml' => 'application/ccxml+xml',
        'cdbcmsg' => 'application/vnd.contact.cmsg',
        'cdkey' => 'application/vnd.mediastation.cdkey',
        'cdmia' => 'application/cdmi-capability',
        'cdmic' => 'application/cdmi-container',
        'cdmid' => 'application/cdmi-domain',
        'cdmio' => 'application/cdmi-object',
        'cdmiq' => 'application/cdmi-queue',
        'cdx' => 'chemical/x-cdx',
        'cdxml' => 'application/vnd.chemdraw+xml',
        'cdy' => 'application/vnd.cinderella',
        'cer' => 'application/pkix-cert',
        'cgm' => 'image/cgm',
        'chat' => 'application/x-chat',
        'chm' => 'application/vnd.ms-htmlhelp',
        'chrt' => 'application/vnd.kde.kchart',
        'cif' => 'chemical/x-cif',
        'cii' => 'application/vnd.anser-web-certificate-issue-initiation',
        'cil' => 'application/vnd.ms-artgalry',
        'cla' => 'application/vnd.claymore',
        'class' => 'application/java-vm',
        'clkk' => 'application/vnd.crick.clicker.keyboard',
        'clkp' => 'application/vnd.crick.clicker.palette',
        'clkt' => 'application/vnd.crick.clicker.template',
        'clkw' => 'application/vnd.crick.clicker.wordbank',
        'clkx' => 'application/vnd.crick.clicker',
        'clp' => 'application/x-msclip',
        'cmc' => 'application/vnd.cosmocaller',
        'cmdf' => 'chemical/x-cmdf',
        'cml' => 'chemical/x-cml',
        'cmp' => 'application/vnd.yellowriver-custom-menu',
        'cmx' => 'image/x-cmx',
        'cod' => 'application/vnd.rim.cod',
        'cpio' => 'application/x-cpio',
        'cpt' => 'application/mac-compactpro',
        'crd' => 'application/x-mscardfile',
        'crl' => 'application/pkix-crl',
        'cryptonote' => 'application/vnd.rig.cryptonote',
        'csh' => 'application/x-csh',
        'csml' => 'chemical/x-csml',
        'csp' => 'application/vnd.commonspace',
        'css' => 'text/css',
        'csv' => 'text/csv',
        'cu' => 'application/cu-seeme',
        'curl' => 'text/vnd.curl',
        'cww' => 'application/prs.cww',
        'dae' => 'model/vnd.collada+xml',
        'daf' => 'application/vnd.mobius.daf',
        'davmount' => 'application/davmount+xml',
        'dcurl' => 'text/vnd.curl.dcurl',
        'dd2' => 'application/vnd.oma.dd2+xml',
        'ddd' => 'application/vnd.fujixerox.ddd',
        'deb' => 'application/x-debian-package',
        'der' => 'application/x-x509-ca-cert',
        'dfac' => 'application/vnd.dreamfactory',
        'dir' => 'application/x-director',
        'dis' => 'application/vnd.mobius.dis',
        'djvu' => 'image/vnd.djvu',
        'dmg' => 'application/x-apple-diskimage',
        'dna' => 'application/vnd.dna',
        'doc' => 'application/msword',
        'docm' => 'application/vnd.ms-word.document.macroenabled.12',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'dotm' => 'application/vnd.ms-word.template.macroenabled.12',
        'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
        'dp' => 'application/vnd.osgi.dp',
        'dpg' => 'application/vnd.dpgraph',
        'dra' => 'audio/vnd.dra',
        'dsc' => 'text/prs.lines.tag',
        'dssc' => 'application/dssc+der',
        'dtb' => 'application/x-dtbook+xml',
        'dtd' => 'application/xml-dtd',
        'dts' => 'audio/vnd.dts',
        'dtshd' => 'audio/vnd.dts.hd',
        'dvi' => 'application/x-dvi',
        'dwf' => 'model/vnd.dwf',
        'dwg' => 'image/vnd.dwg',
        'dxf' => 'image/vnd.dxf',
        'dxp' => 'application/vnd.spotfire.dxp',
        'ecelp4800' => 'audio/vnd.nuera.ecelp4800',
        'ecelp7470' => 'audio/vnd.nuera.ecelp7470',
        'ecelp9600' => 'audio/vnd.nuera.ecelp9600',
        'edm' => 'application/vnd.novadigm.edm',
        'edx' => 'application/vnd.novadigm.edx',
        'efif' => 'application/vnd.picsel',
        'ei6' => 'application/vnd.pg.osasli',
        'eml' => 'message/rfc822',
        'emma' => 'application/emma+xml',
        'eol' => 'audio/vnd.digital-winds',
        'eot' => 'application/vnd.ms-fontobject',
        'epub' => 'application/epub+zip',
        'es' => 'application/ecmascript',
        'es3' => 'application/vnd.eszigno3+xml',
        'esf' => 'application/vnd.epson.esf',
        'etx' => 'text/x-setext',
        'exe' => 'application/x-msdownload',
        'exi' => 'application/exi',
        'ext' => 'application/vnd.novadigm.ext',
        'ez2' => 'application/vnd.ezpix-album',
        'ez3' => 'application/vnd.ezpix-package',
        'f' => 'text/x-fortran',
        'f4v' => 'video/x-f4v',
        'fbs' => 'image/vnd.fastbidsheet',
        'fcs' => 'application/vnd.isac.fcs',
        'fdf' => 'application/vnd.fdf',
        'fe_launch' => 'application/vnd.denovo.fcselayout-link',
        'fg5' => 'application/vnd.fujitsu.oasysgp',
        'fh' => 'image/x-freehand',
        'fig' => 'application/x-xfig',
        'flac' => 'audio/ogg',
        'fli' => 'video/x-fli',
        'flo' => 'application/vnd.micrografx.flo',
        'flv' => 'video/x-flv',
        'flw' => 'application/vnd.kde.kivio',
        'flx' => 'text/vnd.fmi.flexstor',
        'fly' => 'text/vnd.fly',
        'fm' => 'application/vnd.framemaker',
        'fnc' => 'application/vnd.frogans.fnc',
        'fpx' => 'image/vnd.fpx',
        'fsc' => 'application/vnd.fsc.weblaunch',
        'fst' => 'image/vnd.fst',
        'ftc' => 'application/vnd.fluxtime.clip',
        'fti' => 'application/vnd.anser-web-funds-transfer-initiation',
        'fvt' => 'video/vnd.fvt',
        'fxp' => 'application/vnd.adobe.fxp',
        'fzs' => 'application/vnd.fuzzysheet',
        'g2w' => 'application/vnd.geoplan',
        'g3' => 'image/g3fax',
        'g3w' => 'application/vnd.geospace',
        'gac' => 'application/vnd.groove-account',
        'gdl' => 'model/vnd.gdl',
        'geo' => 'application/vnd.dynageo',
        'gex' => 'application/vnd.geometry-explorer',
        'ggb' => 'application/vnd.geogebra.file',
        'ggt' => 'application/vnd.geogebra.tool',
        'ghf' => 'application/vnd.groove-help',
        'gif' => 'image/gif',
        'gim' => 'application/vnd.groove-identity-message',
        'gmx' => 'application/vnd.gmx',
        'gnumeric' => 'application/x-gnumeric',
        'gph' => 'application/vnd.flographit',
        'gpx' => 'application/gpx+xml',
        'gqf' => 'application/vnd.grafeq',
        'gram' => 'application/srgs',
        'grv' => 'application/vnd.groove-injector',
        'grxml' => 'application/srgs+xml',
        'gsf' => 'application/x-font-ghostscript',
        'gtar' => 'application/x-gtar',
        'gtm' => 'application/vnd.groove-tool-message',
        'gtw' => 'model/vnd.gtw',
        'gv' => 'text/vnd.graphviz',
        'gxt' => 'application/vnd.geonext',
        'h261' => 'video/h261',
        'h263' => 'video/h263',
        'h264' => 'video/h264',
        'hal' => 'application/vnd.hal+xml',
        'hbci' => 'application/vnd.hbci',
        'hdf' => 'application/x-hdf',
        'hlp' => 'application/winhlp',
        'hpgl' => 'application/vnd.hp-hpgl',
        'hpid' => 'application/vnd.hp-hpid',
        'hps' => 'application/vnd.hp-hps',
        'hqx' => 'application/mac-binhex40',
        'htke' => 'application/vnd.kenameaapp',
        'html' => 'text/html',
        'hvd' => 'application/vnd.yamaha.hv-dic',
        'hvp' => 'application/vnd.yamaha.hv-voice',
        'hvs' => 'application/vnd.yamaha.hv-script',
        'i2g' => 'application/vnd.intergeo',
        'icc' => 'application/vnd.iccprofile',
        'ice' => 'x-conference/x-cooltalk',
        'ico' => 'image/x-icon',
        'ics' => 'text/calendar',
        'ief' => 'image/ief',
        'ifm' => 'application/vnd.shana.informed.formdata',
        'igl' => 'application/vnd.igloader',
        'igm' => 'application/vnd.insors.igm',
        'igs' => 'model/iges',
        'igx' => 'application/vnd.micrografx.igx',
        'iif' => 'application/vnd.shana.informed.interchange',
        'imp' => 'application/vnd.accpac.simply.imp',
        'ims' => 'application/vnd.ms-ims',
        'ipfix' => 'application/ipfix',
        'ipk' => 'application/vnd.shana.informed.package',
        'irm' => 'application/vnd.ibm.rights-management',
        'irp' => 'application/vnd.irepository.package+xml',
        'itp' => 'application/vnd.shana.informed.formtemplate',
        'ivp' => 'application/vnd.immervision-ivp',
        'ivu' => 'application/vnd.immervision-ivu',
        'jad' => 'text/vnd.sun.j2me.app-descriptor',
        'jam' => 'application/vnd.jam',
        'jar' => 'application/java-archive',
        'jisp' => 'application/vnd.jisp',
        'jlt' => 'application/vnd.hp-jlyt',
        'jnlp' => 'application/x-java-jnlp-file',
        'joda' => 'application/vnd.joost.joda-archive',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'jpgv' => 'video/jpeg',
        'jpm' => 'video/jpm',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'karbon' => 'application/vnd.kde.karbon',
        'kfo' => 'application/vnd.kde.kformula',
        'kia' => 'application/vnd.kidspiration',
        'kml' => 'application/vnd.google-earth.kml+xml',
        'kmz' => 'application/vnd.google-earth.kmz',
        'kne' => 'application/vnd.kinar',
        'kon' => 'application/vnd.kde.kontour',
        'kpr' => 'application/vnd.kde.kpresenter',
        'ksp' => 'application/vnd.kde.kspread',
        'ktx' => 'image/ktx',
        'ktz' => 'application/vnd.kahootz',
        'kwd' => 'application/vnd.kde.kword',
        'lasxml' => 'application/vnd.las.las+xml',
        'latex' => 'application/x-latex',
        'lbd' => 'application/vnd.llamagraphics.life-balance.desktop',
        'lbe' => 'application/vnd.llamagraphics.life-balance.exchange+xml',
        'les' => 'application/vnd.hhe.lesson-player',
        'link66' => 'application/vnd.route66.link66+xml',
        'lrm' => 'application/vnd.ms-lrm',
        'ltf' => 'application/vnd.frogans.ltf',
        'lvp' => 'audio/vnd.lucent.voice',
        'lwp' => 'application/vnd.lotus-wordpro',
        'm21' => 'application/mp21',
        'm3u' => 'audio/x-mpegurl',
        'm3u8' => 'application/vnd.apple.mpegurl',
        'm4v' => 'video/x-m4v',
        'ma' => 'application/mathematica',
        'mads' => 'application/mads+xml',
        'mag' => 'application/vnd.ecowin.chart',
        'mathml' => 'application/mathml+xml',
        'mbk' => 'application/vnd.mobius.mbk',
        'mbox' => 'application/mbox',
        'mc1' => 'application/vnd.medcalcdata',
        'mcd' => 'application/vnd.mcd',
        'mcurl' => 'text/vnd.curl.mcurl',
        'mdb' => 'application/x-msaccess',
        'mdi' => 'image/vnd.ms-modi',
        'meta4' => 'application/metalink4+xml',
        'mets' => 'application/mets+xml',
        'mfm' => 'application/vnd.mfmp',
        'mgp' => 'application/vnd.osgeo.mapguide.package',
        'mgz' => 'application/vnd.proteus.magazine',
        'mid' => 'audio/midi',
        'mif' => 'application/vnd.mif',
        'mj2' => 'video/mj2',
        'mlp' => 'application/vnd.dolby.mlp',
        'mmd' => 'application/vnd.chipnuts.karaoke-mmd',
        'mmf' => 'application/vnd.smaf',
        'mmr' => 'image/vnd.fujixerox.edmics-mmr',
        'mny' => 'application/x-msmoney',
        'mods' => 'application/mods+xml',
        'movie' => 'video/x-sgi-movie',
        'mp4' => 'video/mp4',
        'mp4a' => 'audio/mp4',
        'mpc' => 'application/vnd.mophun.certificate',
        'mpeg' => 'video/mpeg',
        'mpga' => 'audio/mpeg',
        'mpkg' => 'application/vnd.apple.installer+xml',
        'mpm' => 'application/vnd.blueice.multipass',
        'mpn' => 'application/vnd.mophun.application',
        'mpp' => 'application/vnd.ms-project',
        'mpy' => 'application/vnd.ibm.minipay',
        'mqy' => 'application/vnd.mobius.mqy',
        'mrc' => 'application/marc',
        'mrcx' => 'application/marcxml+xml',
        'mscml' => 'application/mediaservercontrol+xml',
        'mseq' => 'application/vnd.mseq',
        'msf' => 'application/vnd.epson.msf',
        'msh' => 'model/mesh',
        'msl' => 'application/vnd.mobius.msl',
        'msty' => 'application/vnd.muvee.style',
        'mts' => 'model/vnd.mts',
        'mus' => 'application/vnd.musician',
        'musicxml' => 'application/vnd.recordare.musicxml+xml',
        'mvb' => 'application/x-msmediaview',
        'mwf' => 'application/vnd.mfer',
        'mxf' => 'application/mxf',
        'mxl' => 'application/vnd.recordare.musicxml',
        'mxml' => 'application/xv+xml',
        'mxs' => 'application/vnd.triscape.mxs',
        'mxu' => 'video/vnd.mpegurl',
        'n3' => 'text/n3',
        'nbp' => 'application/vnd.wolfram.player',
        'nc' => 'application/x-netcdf',
        'ncx' => 'application/x-dtbncx+xml',
        'n-gage' => 'application/vnd.nokia.n-gage.symbian.install',
        'ngdat' => 'application/vnd.nokia.n-gage.data',
        'nlu' => 'application/vnd.neurolanguage.nlu',
        'nml' => 'application/vnd.enliven',
        'nnd' => 'application/vnd.noblenet-directory',
        'nns' => 'application/vnd.noblenet-sealer',
        'nnw' => 'application/vnd.noblenet-web',
        'npx' => 'image/vnd.net-fpx',
        'nsf' => 'application/vnd.lotus-notes',
        'oa2' => 'application/vnd.fujitsu.oasys2',
        'oa3' => 'application/vnd.fujitsu.oasys3',
        'oas' => 'application/vnd.fujitsu.oasys',
        'obd' => 'application/x-msbinder',
        'oda' => 'application/oda',
        'odb' => 'application/vnd.oasis.opendocument.database',
        'odc' => 'application/vnd.oasis.opendocument.chart',
        'odf' => 'application/vnd.oasis.opendocument.formula',
        'odft' => 'application/vnd.oasis.opendocument.formula-template',
        'odg' => 'application/vnd.oasis.opendocument.graphics',
        'odi' => 'application/vnd.oasis.opendocument.image',
        'odm' => 'application/vnd.oasis.opendocument.text-master',
        'odp' => 'application/vnd.oasis.opendocument.presentation',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        'odt' => 'application/vnd.oasis.opendocument.text',
        'oga' => 'audio/ogg',
        'ogv' => 'video/ogg',
        'ogx' => 'application/ogg',
        'onetoc' => 'application/onenote',
        'opf' => 'application/oebps-package+xml',
        'org' => 'application/vnd.lotus-organizer',
        'osf' => 'application/vnd.yamaha.openscoreformat',
        'osfpvg' => 'application/vnd.yamaha.openscoreformat.osfpvg+xml',
        'otc' => 'application/vnd.oasis.opendocument.chart-template',
        'otf' => 'application/x-font-otf',
        'otg' => 'application/vnd.oasis.opendocument.graphics-template',
        'oth' => 'application/vnd.oasis.opendocument.text-web',
        'oti' => 'application/vnd.oasis.opendocument.image-template',
        'otp' => 'application/vnd.oasis.opendocument.presentation-template',
        'ots' => 'application/vnd.oasis.opendocument.spreadsheet-template',
        'ott' => 'application/vnd.oasis.opendocument.text-template',
        'oxt' => 'application/vnd.openofficeorg.extension',
        'p' => 'text/x-pascal',
        'p10' => 'application/pkcs10',
        'p12' => 'application/x-pkcs12',
        'p7b' => 'application/x-pkcs7-certificates',
        'p7m' => 'application/pkcs7-mime',
        'p7r' => 'application/x-pkcs7-certreqresp',
        'p7s' => 'application/pkcs7-signature',
        'p8' => 'application/pkcs8',
        'par' => 'text/plain-bas',
        'paw' => 'application/vnd.pawaafile',
        'pbd' => 'application/vnd.powerbuilder6',
        'pbm' => 'image/x-portable-bitmap',
        'pcf' => 'application/x-font-pcf',
        'pcl' => 'application/vnd.hp-pcl',
        'pclxl' => 'application/vnd.hp-pclxl',
        'pcurl' => 'application/vnd.curl.pcurl',
        'pcx' => 'image/x-pcx',
        'pdb' => 'application/vnd.palm',
        'pdf' => 'application/pdf',
        'pfa' => 'application/x-font-type1',
        'pfr' => 'application/font-tdpfr',
        'pgm' => 'image/x-portable-graymap',
        'pgn' => 'application/x-chess-pgn',
        'pgp' => 'application/pgp-encrypted',
        'pic' => 'image/x-pict',
        'pjpeg' => 'image/pjpeg',
        'pki' => 'application/pkixcmp',
        'pkipath' => 'application/pkix-pkipath',
        'plb' => 'application/vnd.3gpp.pic-bw-large',
        'plc' => 'application/vnd.mobius.plc',
        'plf' => 'application/vnd.pocketlearn',
        'pls' => 'application/pls+xml',
        'pml' => 'application/vnd.ctc-posml',
        'png' => 'image/png',
        'pnm' => 'image/x-portable-anymap',
        'portpkg' => 'application/vnd.macports.portpkg',
        'potm' => 'application/vnd.ms-powerpoint.template.macroenabled.12',
        'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
        'ppam' => 'application/vnd.ms-powerpoint.addin.macroenabled.12',
        'ppd' => 'application/vnd.cups-ppd',
        'ppm' => 'image/x-portable-pixmap',
        'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroenabled.12',
        'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptm' => 'application/vnd.ms-powerpoint.presentation.macroenabled.12',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'prc' => 'application/x-mobipocket-ebook',
        'pre' => 'application/vnd.lotus-freelance',
        'prf' => 'application/pics-rules',
        'psb' => 'application/vnd.3gpp.pic-bw-small',
        'psd' => 'image/vnd.adobe.photoshop',
        'psf' => 'application/x-font-linux-psf',
        'pskcxml' => 'application/pskc+xml',
        'ptid' => 'application/vnd.pvi.ptid1',
        'pub' => 'application/x-mspublisher',
        'pvb' => 'application/vnd.3gpp.pic-bw-var',
        'pwn' => 'application/vnd.3m.post-it-notes',
        'pya' => 'audio/vnd.ms-playready.media.pya',
        'pyv' => 'video/vnd.ms-playready.media.pyv',
        'qam' => 'application/vnd.epson.quickanime',
        'qbo' => 'application/vnd.intu.qbo',
        'qfx' => 'application/vnd.intu.qfx',
        'qps' => 'application/vnd.publishare-delta-tree',
        'qt' => 'video/quicktime',
        'qxd' => 'application/vnd.quark.quarkxpress',
        'ram' => 'audio/x-pn-realaudio',
        'rar' => 'application/x-rar-compressed',
        'ras' => 'image/x-cmu-raster',
        'rcprofile' => 'application/vnd.ipunplugged.rcprofile',
        'rdf' => 'application/rdf+xml',
        'rdz' => 'application/vnd.data-vision.rdz',
        'rep' => 'application/vnd.businessobjects',
        'res' => 'application/x-dtbresource+xml',
        'rgb' => 'image/x-rgb',
        'rif' => 'application/reginfo+xml',
        'rip' => 'audio/vnd.rip',
        'rl' => 'application/resource-lists+xml',
        'rlc' => 'image/vnd.fujixerox.edmics-rlc',
        'rld' => 'application/resource-lists-diff+xml',
        'rm' => 'application/vnd.rn-realmedia',
        'rmp' => 'audio/x-pn-realaudio-plugin',
        'rms' => 'application/vnd.jcp.javame.midlet-rms',
        'rnc' => 'application/relax-ng-compact-syntax',
        'rp9' => 'application/vnd.cloanto.rp9',
        'rpss' => 'application/vnd.nokia.radio-presets',
        'rpst' => 'application/vnd.nokia.radio-preset',
        'rq' => 'application/sparql-query',
        'rs' => 'application/rls-services+xml',
        'rsd' => 'application/rsd+xml',
        'rss' => 'application/rss+xml',
        'rtf' => 'application/rtf',
        'rtx' => 'text/richtext',
        's' => 'text/x-asm',
        'saf' => 'application/vnd.yamaha.smaf-audio',
        'sbml' => 'application/sbml+xml',
        'sc' => 'application/vnd.ibm.secure-container',
        'scd' => 'application/x-msschedule',
        'scm' => 'application/vnd.lotus-screencam',
        'scq' => 'application/scvp-cv-request',
        'scs' => 'application/scvp-cv-response',
        'scurl' => 'text/vnd.curl.scurl',
        'sda' => 'application/vnd.stardivision.draw',
        'sdc' => 'application/vnd.stardivision.calc',
        'sdd' => 'application/vnd.stardivision.impress',
        'sdkm' => 'application/vnd.solent.sdkm+xml',
        'sdp' => 'application/sdp',
        'sdw' => 'application/vnd.stardivision.writer',
        'see' => 'application/vnd.seemail',
        'seed' => 'application/vnd.fdsn.seed',
        'sema' => 'application/vnd.sema',
        'semd' => 'application/vnd.semd',
        'semf' => 'application/vnd.semf',
        'ser' => 'application/java-serialized-object',
        'setpay' => 'application/set-payment-initiation',
        'setreg' => 'application/set-registration-initiation',
        'sfd-hdstx' => 'application/vnd.hydrostatix.sof-data',
        'sfs' => 'application/vnd.spotfire.sfs',
        'sgl' => 'application/vnd.stardivision.writer-global',
        'sgml' => 'text/sgml',
        'sh' => 'application/x-sh',
        'shar' => 'application/x-shar',
        'shf' => 'application/shf+xml',
        'sis' => 'application/vnd.symbian.install',
        'sit' => 'application/x-stuffit',
        'sitx' => 'application/x-stuffitx',
        'skp' => 'application/vnd.koan',
        'sldm' => 'application/vnd.ms-powerpoint.slide.macroenabled.12',
        'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
        'slt' => 'application/vnd.epson.salt',
        'sm' => 'application/vnd.stepmania.stepchart',
        'smf' => 'application/vnd.stardivision.math',
        'smi' => 'application/smil+xml',
        'snf' => 'application/x-font-snf',
        'spf' => 'application/vnd.yamaha.smaf-phrase',
        'spl' => 'application/x-futuresplash',
        'spot' => 'text/vnd.in3d.spot',
        'spp' => 'application/scvp-vp-response',
        'spq' => 'application/scvp-vp-request',
        'src' => 'application/x-wais-source',
        'sru' => 'application/sru+xml',
        'srx' => 'application/sparql-results+xml',
        'sse' => 'application/vnd.kodak-descriptor',
        'ssf' => 'application/vnd.epson.ssf',
        'ssml' => 'application/ssml+xml',
        'st' => 'application/vnd.sailingtracker.track',
        'stc' => 'application/vnd.sun.xml.calc.template',
        'std' => 'application/vnd.sun.xml.draw.template',
        'stf' => 'application/vnd.wt.stf',
        'sti' => 'application/vnd.sun.xml.impress.template',
        'stk' => 'application/hyperstudio',
        'stl' => 'application/vnd.ms-pki.stl',
        'str' => 'application/vnd.pg.format',
        'stw' => 'application/vnd.sun.xml.writer.template',
        'sub' => 'image/vnd.dvb.subtitle',
        'sus' => 'application/vnd.sus-calendar',
        'sv4cpio' => 'application/x-sv4cpio',
        'sv4crc' => 'application/x-sv4crc',
        'svc' => 'application/vnd.dvb.service',
        'svd' => 'application/vnd.svd',
        'svg' => 'image/svg+xml',
        'swf' => 'application/x-shockwave-flash',
        'swi' => 'application/vnd.aristanetworks.swi',
        'sxc' => 'application/vnd.sun.xml.calc',
        'sxd' => 'application/vnd.sun.xml.draw',
        'sxg' => 'application/vnd.sun.xml.writer.global',
        'sxi' => 'application/vnd.sun.xml.impress',
        'sxm' => 'application/vnd.sun.xml.math',
        'sxw' => 'application/vnd.sun.xml.writer',
        't' => 'text/troff',
        'tao' => 'application/vnd.tao.intent-module-archive',
        'tar' => 'application/x-tar',
        'tcap' => 'application/vnd.3gpp2.tcap',
        'tcl' => 'application/x-tcl',
        'teacher' => 'application/vnd.smart.teacher',
        'tei' => 'application/tei+xml',
        'tex' => 'application/x-tex',
        'texinfo' => 'application/x-texinfo',
        'tfi' => 'application/thraud+xml',
        'tfm' => 'application/x-tex-tfm',
        'thmx' => 'application/vnd.ms-officetheme',
        'tiff' => 'image/tiff',
        'tmo' => 'application/vnd.tmobile-livetv',
        'torrent' => 'application/x-bittorrent',
        'tpl' => 'application/vnd.groove-tool-template',
        'tpt' => 'application/vnd.trid.tpt',
        'tra' => 'application/vnd.trueapp',
        'trm' => 'application/x-msterminal',
        'tsd' => 'application/timestamped-data',
        'tsv' => 'text/tab-separated-values',
        'ttf' => 'application/x-font-ttf',
        'ttl' => 'text/turtle',
        'twd' => 'application/vnd.simtech-mindmapper',
        'txd' => 'application/vnd.genomatix.tuxedo',
        'txf' => 'application/vnd.mobius.txf',
        'txt' => 'text/plain',
        'ufd' => 'application/vnd.ufdl',
        'umj' => 'application/vnd.umajin',
        'unityweb' => 'application/vnd.unity',
        'uoml' => 'application/vnd.uoml+xml',
        'uri' => 'text/uri-list',
        'ustar' => 'application/x-ustar',
        'utz' => 'application/vnd.uiq.theme',
        'uu' => 'text/x-uuencode',
        'uva' => 'audio/vnd.dece.audio',
        'uvh' => 'video/vnd.dece.hd',
        'uvi' => 'image/vnd.dece.graphic',
        'uvm' => 'video/vnd.dece.mobile',
        'uvp' => 'video/vnd.dece.pd',
        'uvs' => 'video/vnd.dece.sd',
        'uvu' => 'video/vnd.uvvu.mp4',
        'uvv' => 'video/vnd.dece.video',
        'vcd' => 'application/x-cdlink',
        'vcf' => 'text/x-vcard',
        'vcg' => 'application/vnd.groove-vcard',
        'vcs' => 'text/x-vcalendar',
        'vcx' => 'application/vnd.vcx',
        'vis' => 'application/vnd.visionary',
        'viv' => 'video/vnd.vivo',
        'vsd' => 'application/vnd.visio',
        'vsdx' => 'application/vnd.visio2013',
        'vsf' => 'application/vnd.vsf',
        'vtu' => 'model/vnd.vtu',
        'vxml' => 'application/voicexml+xml',
        'wad' => 'application/x-doom',
        'wav' => 'audio/x-wav',
        'wax' => 'audio/x-ms-wax',
        'wbmp' => 'image/vnd.wap.wbmp',
        'wbs' => 'application/vnd.criticaltools.wbs+xml',
        'wbxml' => 'application/vnd.wap.wbxml',
        'weba' => 'audio/webm',
        'webm' => 'video/webm',
        'webp' => 'image/webp',
        'wg' => 'application/vnd.pmi.widget',
        'wgt' => 'application/widget',
        'wm' => 'video/x-ms-wm',
        'wma' => 'audio/x-ms-wma',
        'wmd' => 'application/x-ms-wmd',
        'wmf' => 'application/x-msmetafile',
        'wml' => 'text/vnd.wap.wml',
        'wmlc' => 'application/vnd.wap.wmlc',
        'wmls' => 'text/vnd.wap.wmlscript',
        'wmlsc' => 'application/vnd.wap.wmlscriptc',
        'wmv' => 'video/x-ms-wmv',
        'wmx' => 'video/x-ms-wmx',
        'wmz' => 'application/x-ms-wmz',
        'woff' => 'application/x-font-woff',
        'wpd' => 'application/vnd.wordperfect',
        'wpl' => 'application/vnd.ms-wpl',
        'wps' => 'application/vnd.ms-works',
        'wqd' => 'application/vnd.wqd',
        'wri' => 'application/x-mswrite',
        'wrl' => 'model/vrml',
        'wsdl' => 'application/wsdl+xml',
        'wspolicy' => 'application/wspolicy+xml',
        'wtb' => 'application/vnd.webturbo',
        'wvx' => 'video/x-ms-wvx',
        'x3d' => 'application/vnd.hzn-3d-crossword',
        'xap' => 'application/x-silverlight-app',
        'xar' => 'application/vnd.xara',
        'xbap' => 'application/x-ms-xbap',
        'xbd' => 'application/vnd.fujixerox.docuworks.binder',
        'xbm' => 'image/x-xbitmap',
        'xdf' => 'application/xcap-diff+xml',
        'xdm' => 'application/vnd.syncml.dm+xml',
        'xdp' => 'application/vnd.adobe.xdp+xml',
        'xdssc' => 'application/dssc+xml',
        'xdw' => 'application/vnd.fujixerox.docuworks',
        'xenc' => 'application/xenc+xml',
        'xer' => 'application/patch-ops-error+xml',
        'xfdf' => 'application/vnd.adobe.xfdf',
        'xfdl' => 'application/vnd.xfdl',
        'xhtml' => 'application/xhtml+xml',
        'xif' => 'image/vnd.xiff',
        'xlam' => 'application/vnd.ms-excel.addin.macroenabled.12',
        'xls' => 'application/vnd.ms-excel',
        'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroenabled.12',
        'xlsm' => 'application/vnd.ms-excel.sheet.macroenabled.12',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xltm' => 'application/vnd.ms-excel.template.macroenabled.12',
        'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
        'xml' => 'application/xml',
        'xo' => 'application/vnd.olpc-sugar',
        'xop' => 'application/xop+xml',
        'xpi' => 'application/x-xpinstall',
        'xpm' => 'image/x-xpixmap',
        'xpr' => 'application/vnd.is-xpr',
        'xps' => 'application/vnd.ms-xpsdocument',
        'xpw' => 'application/vnd.intercon.formnet',
        'xslt' => 'application/xslt+xml',
        'xsm' => 'application/vnd.syncml+xml',
        'xspf' => 'application/xspf+xml',
        'xul' => 'application/vnd.mozilla.xul+xml',
        'xwd' => 'image/x-xwindowdump',
        'xyz' => 'chemical/x-xyz',
        'yaml' => 'text/yaml',
        'yang' => 'application/yang',
        'yin' => 'application/yin+xml',
        'zaz' => 'application/vnd.zzazz.deck+xml',
        'zip' => 'application/zip',
        'zir' => 'application/vnd.zul',
        'zmm' => 'application/vnd.handheld-entertainment+xml',
    ];
    #Regex for language tag as per https://tools.ietf.org/html/rfc5646#section-2.1. Taken from https://stackoverflow.com/questions/7035825/regular-expression-for-a-language-tag-as-defined-by-bcp47
    const langTagRegex = '(?<grandfathered>(?:en-GB-oed|i-(?:ami|bnn|default|enochian|hak|klingon|lux|mingo|navajo|pwn|t(?:a[oy]|su))|sgn-(?:BE-(?:FR|NL)|CH-DE))|(?:art-lojban|cel-gaulish|no-(?:bok|nyn)|zh-(?:guoyu|hakka|min(?:-nan)?|xiang)))|(?:(?<language>(?:[A-Za-z]{2,3}(?:-(?<extlang>[A-Za-z]{3}(?:-[A-Za-z]{3}){0,2}))?)|[A-Za-z]{4}|[A-Za-z]{5,8})(?:-(?<script>[A-Za-z]{4}))?(?:-(?<region>[A-Za-z]{2}|[0-9]{3}))?(?:-(?<variant>[A-Za-z0-9]{5,8}|[0-9][A-Za-z0-9]{3}))*(?:-(?<extension>[0-9A-WY-Za-wy-z](?:-[A-Za-z0-9]{2,8})+))*)(?:-(?<privateUse>x(?:-[A-Za-z0-9]{1,8})+))?';
    
    #Function for smart resumable download with proper headers
    public function download(string $file, string $filename = '', string $mime = '', bool $inline = false, int $speedlimit = 10485760, bool $exit = true)
    {
        #Sanitize speedlimit
        $speedlimit = $this->speedLimit($speedlimit);
        #Some protection
        header('Access-Control-Allow-Headers: range');
        header('Access-Control-Allow-Methods: GET');
        #Download is valid only in case of GET method
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return (new \http20\Headers)->clientReturn('405', $exit);
        }
        #Check that path exists and is actually a file
        if (!is_file($file)) {
            if (isset($_SERVER['HTTP_RANGE'])) {
                return (new \http20\Headers)->clientReturn('410', $exit);
            } else {
                return (new \http20\Headers)->clientReturn('404', $exit);
            }
        }
        #Check if file is readable
        if (!is_readable($file)) {
            return (new \http20\Headers)->clientReturn('409', $exit);
        }
        #Get file information (we need extension and basename)
        $fileinfo = pathinfo($file);
        #Get file size
        $filesize = filesize($file);
        #Get MD5 to use as boundary in case we have a multipart download
        $boundary = hash_file('md5', $file);
        #Control caching
        header('Cache-Control: must-revalidate, no-transform');
        #If file has been cached by browser since last time it has been changed - exit before everything. Depends on browser whether this will work, though.
        (new \http20\Headers)->lastModified(filemtime($file), true)->eTag($boundary);
        #Check if MIME was provided
        if (!empty($mime)) {
            #If yes, validate its format
            if (preg_match('/^(('.self::mimeRegex.') ?){1,}$/i', $value) !== 1) {
                #Empty invalid MIME
                $mime = '';
            }
        }
        #Check if it's empty again (or was from the start)
        if (empty($mime)) {
            #If not, attempt to check if in the constant list based on extesnion
            if (isset(self::extToMime[$fileinfo['extension']])) {
                $mime = self::extToMime[$fileinfo['extension']];
            } else {
                #Replace MIME
                $mime = 'application/octet-stream';
            }
        }
        #Get file name
        if (empty($filename)) {
            $filename = $fileinfo['basename'];
        }
        #Process ranges
        $ranges = $this->rangesValidate($filesize);
        if (isset($ranges[0]) && $ranges[0] === false) {
            return (new \http20\Headers)->clientReturn('416', $exit);
        }
        #Send common headers
        if ($inline) {
            header('Content-Disposition: inline; filename="'.$filename.'"');
        } else {
            header('Content-Disposition: attachment; filename="'.$filename.'"');
        }
        #Notify, that we accept ranges
        header('Accept-Ranges: bytes');
        #Generally not required for web, but in case this somehow gets into a mail - better have it
        header('Content-Transfer-Encoding: binary');
        #Open the file
        $stream = fopen($file, 'rb');
        #Check if file was opened
        if ($stream === false) {
            return (new \http20\Headers)->clientReturn('500', $exit);
        }
        #Open output stream
        $output = fopen('php://output', 'wb');
        #Check if stream was opened
        if ($output === false) {
            return (new \http20\Headers)->clientReturn('500', $exit);
        }
        #Disable buffering. This should help limiting the memory usage. At least, in some cases.
        stream_set_read_buffer($stream, 0);
        stream_set_write_buffer($output, 0);
        if (!empty($ranges)) {
            #Send partial content headers
            header($_SERVER['SERVER_PROTOCOL'].' 206 Partial Content');
            #Checking how many ranges we have
            if (count($ranges) === 1) {
                header('Content-Type: '.$mime);
                header('Content-Range: bytes '.$ranges[0]['start'].'-'.$ranges[0]['end'].'/'.$filesize);
                #Update size to block size
                $filesize = $ranges[0]['end'] - $ranges[0]['start'] + 1;
                header('Content-Length: '.$filesize);
                #Limit speed to range length, if it's current speed limit is too large, so that it will be provided fully
                if ($speedlimit > $filesize) {
                    $speedlimit = $filesize;
                }
                $speedlimit = $this->speedLimit($speedlimit);
                #Output data
                $result = $this->streamCopy($stream, $output, $filesize, $ranges[0]['start'], $speedlimit);
                #Close file
                fclose($stream);
                fclose($output);
                if ($result === false) {
                    return (new \http20\Headers)->clientReturn('500', $exit);
                } else {
                    if ($exit) {
                        return (new \http20\Headers)->clientReturn('200', true);
                    } else {
                        return $result;
                    }
                }
            } else {
                header('Content-Type: multipart/byteranges; boundary='.$boundary);
                #Calculate size starting with the mandatory end of the feed (delimiter)
                $partsSize = strlen("\r\n--".$boundary."\r\n");
                foreach ($ranges as $range) {
                    #Add content size
                    $partsSize += $range['end'] - $range['start'] + 1;
                    #Add size of supportive text
                    $partsSize += strlen("\r\n--".$boundary."\r\n".'Content-type: '.$mime."\r\n".'Content-Range: bytes '.$range['start'].'-'.$range['end'].'/'.$filesize."\r\n\r\n");
                }
                #Send expected size to client
                header('Content-Length: '.$partsSize);
                #Iterrate the parts
                $result = false;
                $sent = 0;
                foreach ($ranges as $range) {
                    #Echo supportive text
                    echo "\r\n--".$boundary."\r\n".'Content-type: '.$mime."\r\n".'Content-Range: bytes '.$range['start'].'-'.$range['end'].'/'.$filesize."\r\n\r\n";
                    #Limit speed to range length, if current speed limit is too large, so that it will be provided fully
                    if ($speedlimit > $range['end'] - $range['start'] + 1) {
                        $speedlimit_multi = $range['end'] - $range['start'] + 1;
                    } else {
                        $speedlimit_multi = $speedlimit;
                    }
                    $speedlimit_multi = $this->speedLimit($speedlimit_multi);
                    #Output data
                    $result = $this->streamCopy($stream, $output, $range['end'] - $range['start'] + 1, $range['start'], $speedlimit_multi);
                    if ($result === false) {
                        fclose($stream);
                        fclose($output);
                        return (new \http20\Headers)->clientReturn('500', $exit);
                    } else {
                        $sent += $result;
                    }
                }
                #Close the file
                fclose($stream);
                fclose($output);
                echo "\r\n--".$boundary."\r\n";
                if ($exit) {
                    return (new \http20\Headers)->clientReturn('200', true);
                } else {
                    return $sent;
                }
            }
        } else {
            header('Content-Type: '.$mime);
            header('Content-Length: '.$filesize);
            header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
            #Output data
            $result = $this->streamCopy($stream, $output, $filesize, 0, $speedlimit);
            #Close the file
            fclose($stream);
            fclose($output);
            if ($result === false) {
                return (new \http20\Headers)->clientReturn('500', $exit);
            } else {
                if ($exit) {
                    return (new \http20\Headers)->clientReturn('200', true);
                } else {
                    return $result;
                }
            }
        }
    }
    
    #Function to handle file uploads
    public function upload($destPath, bool $preserveNames = false, bool $overwrite = false, array $allowedMime = [], bool $intollerant = true, bool $exit = true)
    {
        #Cache some PHP settings
        $maxUpload = $this-> phpMemoryToInt(ini_get('upload_max_filesize'));
        $maxPost = $this-> phpMemoryToInt(ini_get('post_max_size'));
        $maxFiles = ini_get('max_file_uploads');
        #Check if POST or PUT
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
            return (new \http20\Headers)->clientReturn('405', $exit);
        }
        #Check content type if we have POST method
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && (empty($_SERVER['CONTENT_TYPE']) || preg_match('/^multipart\/form-data(;)?.*/i', $_SERVER['CONTENT_TYPE']) !== 1)) {
            return (new \http20\Headers)->clientReturn('415', $exit);
        }
        #Sanitize provided MIME types
        if (!empty($allowedMime)) {
            $mimeRegex = (new \http20\Headers)::mimeRegex;
            foreach ($allowedMime as $key=>$mime) {
                if (preg_match('/^'.$mimeRegex.'$/i', $mime) !== 1) {
                    unset($allowedMime[$key]);
                }
            }
        }
        #Cache filename sanitizer
        if (method_exists('\SafeFileName\SafeFileName','sanitize')) {
            $SafeFileName = (new \SafeFileName\SafeFileName);
        } else {
            $SafeFileName = false;
        }
        #Check if file upload is enabled on server
        if (ini_get('file_uploads') == false) {
            return (new \http20\Headers)->clientReturn('501', $exit);
        }
        #Check that we do have some space allocated for file uploads
        if ($maxUpload === 0 || $maxPost === 0 || $maxFiles === 0) {
            return (new \http20\Headers)->clientReturn('507', $exit);
        }
        #Validate destination directory
        if (is_string($destPath)) {
            $destPath = realpath($destPath);
            if (!is_dir($destPath) || !is_writable($destPath)) {
                return (new \http20\Headers)->clientReturn('500', $exit);
            }
        } elseif (is_array($destPath)) {
            foreach ($destPath as $key=>$path) {
                $destPath[$key] = realpath($path);
                if (!is_dir($destPath[$key]) || !is_writable($destPath[$key])) {
                    return (new \http20\Headers)->clientReturn('500', $exit);
                }
            }
        } else {
            return (new \http20\Headers)->clientReturn('500', $exit);
        }
        #Process files based on method used
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            #Check that something was sent to us at all
            if ((isset($_SERVER['CONTENT_LENGTH']) && intval($_SERVER['CONTENT_LENGTH']) === 0) || empty($_FILES) || empty($_POST)) {
                return (new \http20\Headers)->clientReturn('400', $exit);
            }
            #Standardize $_FILES and also count them
            $totalfiles = 0;
            foreach ($_FILES as $field=>$files) {
                #Check if multiple files were uploaded to a field and process the values accordingly
                if (is_array($files['name'])) {
                    $totalfiles += count($files['name']);
                    foreach ($files['name'] as $key=>$file) {
                        $_FILES[$field][$key]['name'] = $file;
                        $_FILES[$field][$key]['type'] = $files['type'][$key];
                        $_FILES[$field][$key]['size'] = $files['size'][$key];
                        $_FILES[$field][$key]['tmp_name'] = $files['tmp_name'][$key];
                        $_FILES[$field][$key]['error'] = $files['error'][$key];
                    }
                } else {
                    $totalfiles += 1;
                    $_FILES[$field][0]['name'] = $files['name'];
                    $_FILES[$field][0]['type'] = $files['type'];
                    $_FILES[$field][0]['size'] = $files['size'];
                    $_FILES[$field][0]['tmp_name'] = $files['tmp_name'];
                    $_FILES[$field][0]['error'] = $files['error'];
                }
                unset($_FILES[$field]['name'], $_FILES[$field]['type'], $_FILES[$field]['size'], $_FILES[$field]['tmp_name'], $_FILES[$field]['error']);
            }
            #Check number of files
            if ($totalfiles > $maxFiles) {
                return (new \http20\Headers)->clientReturn('413', $exit);
            }
            #Prepare array for uploaded files
            $uploadedFiles = [];
            #Check for any errors in files, so that we can exit before actually processing the rest
            foreach ($_FILES as $field=>$files) {
                #Check that field has a folder to copy file to
                if (is_array($destPath) && !isset($destPath[$field])) {
                    if ($intollerant) {
                        return (new \http20\Headers)->clientReturn('501', $exit);
                    } else {
                        #Remove the file from list
                        unset($_FILES[$field]);
                        continue;
                    }
                }
                #Set destination path
                if (is_string($destPath)) {
                    $finalPath = $destPath;
                } else {
                    $finalPath = $destPath[$field];
                }
                foreach ($files as $key=>$file) {
                    switch ($file['error'])
                    {
                        case UPLOAD_ERR_OK:
                            #Do nothing at this time
                            break;
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            if ($intollerant) {
                                return (new \http20\Headers)->clientReturn('413', $exit);
                            } else {
                                #Remove the file from list
                                unset($_FILES[$field][$key]);
                                continue 2;
                            }
                            break;
                        case UPLOAD_ERR_PARTIAL:
                        case UPLOAD_ERR_NO_FILE:
                        case UPLOAD_ERR_CANT_WRITE:
                            if ($intollerant) {
                                return (new \http20\Headers)->clientReturn('409', $exit);
                            } else {
                                #Remove the file from list
                                unset($_FILES[$field][$key]);
                                continue 2;
                            }
                            break;
                        case UPLOAD_ERR_NO_TMP_DIR:
                        case UPLOAD_ERR_EXTENSION:
                            if ($intollerant) {
                                return (new \http20\Headers)->clientReturn('500', $exit);
                            } else {
                                #Remove the file from list
                                unset($_FILES[$field][$key]);
                                continue 2;
                            }
                            break;
                        default:
                            if ($intollerant) {
                                return (new \http20\Headers)->clientReturn('418', $exit);
                            } else {
                                #Remove the file from list
                                unset($_FILES[$field][$key]);
                                continue 2;
                            }
                            break;
                    }
                    #Check if file being referenced was, indeed, sent to us via POST
                    if (is_uploaded_file($file['tmp_name']) === false) {
                        #Deny further processing. This is the only case, where we ignore $intollerant setting for security reasons
                        return (new \http20\Headers)->clientReturn('403', $exit);
                    }
                    #Check file size
                    if ($file['size'] > $maxUpload) {
                        if ($intollerant) {
                            return (new \http20\Headers)->clientReturn('413', $exit);
                        } else {
                            #Remove the file from list
                            unset($_FILES[$field][$key]);
                            continue;
                        }
                    } elseif ($file['size'] === 0 || empty($file['tmp_name'])) {
                        #Check if tmp_name is set or $file size is empty
                        if ($intollerant) {
                            return (new \http20\Headers)->clientReturn('400', $exit);
                        } else {
                            #Remove the file from list
                            unset($_FILES[$field][$key]);
                            continue;
                        }
                    }
                    #Get actual MIME type
                    if (isset($_FILES[$field][$key])) {
                        if (extension_loaded('fileinfo')) {
                            $_FILES[$field][$key]['type'] = mime_content_type($file['tmp_name']);
                        }
                        #Check against allowed MIME types if any was set and fileinfo is loaded
                        if (!empty($allowedMime)) {
                            #Get MIME from file (not relying on what was sent by client)
                            if (!in_array($_FILES[$field][$key]['type'], $allowedMime)) {
                                if ($intollerant) {
                                    return (new \http20\Headers)->clientReturn('415', $exit);
                                } else {
                                    #Remove the file from list
                                    unset($_FILES[$field][$key]);
                                    continue;
                                }
                            }
                        }
                    }
                    #Sanitize name
                    if (isset($_FILES[$field][$key])) {
                        if ($SafeFileName !== false) {
                            $_FILES[$field][$key]['name'] = basename($SafeFileName->sanitize($file['name']));
                            #If name is empty or name is too long, do not process it
                            if (empty($_FILES[$field][$key]['name']) || mb_strlen($_FILES[$field][$key]['name'], 'UTF-8') > 225) {
                                if ($intollerant) {
                                    return (new \http20\Headers)->clientReturn('400', $exit);
                                } else {
                                    #Remove the file from list
                                    unset($_FILES[$field][$key]);
                                    continue;
                                }
                            } else {
                                #Set new name for the file. By default we will be using hash of the file. Using sha3-256 since it has lower probability of collissions than md5, although we do lose some speed
                                #Hash is saved regardless, though, since it may be very useful
                                $_FILES[$field][$key]['hash'] = hash_file('sha3-256', $file['tmp_name']);
                                if ($preserveNames) {
                                    $_FILES[$field][$key]['new_name'] = $_FILES[$field][$key]['name'];
                                } else {
                                    #Get extension (if any)
                                    $ext = strval(pathinfo($_FILES[$field][$key]['name'])['extension']);
                                    if (!empty($ext)) {
                                        $ext = '.'.$ext;
                                    }
                                    #Generate name from hash and extension from original file
                                    $_FILES[$field][$key]['new_name'] = $_FILES[$field][$key]['hash'].$ext;
                                }
                                #Check if destinatino file already exists
                                if (is_file($finalPath.'/'.$_FILES[$field][$key]['new_name'])) {
                                    if ($overwrite) {
                                        #Check that it is writable
                                        if (!is_writable($finalPath.'/'.$_FILES[$field][$key]['new_name'])) {
                                            if ($intollerant) {
                                                return (new \http20\Headers)->clientReturn('409', $exit);
                                            } else {
                                                #Remove the file from list
                                                unset($_FILES[$field][$key]);
                                                continue;
                                            }
                                        }
                                    } else {
                                        #Add it to the list of successfully uploaded files if we are not preserving names, since that implies relative uniqueness of them, thus we are most likely seeing the same file
                                        if ($preserveNames === false) {
                                            $uploadedFiles[] = ['server_name' => $_FILES[$field][$key]['new_name'], 'user_name' => $_FILES[$field][$key]['name'], 'size' => $file['size'], 'type' => $_FILES[$field][$key]['type'], 'hash' => $_FILES[$field][$key]['hash'], 'field' => $field];
                                        }
                                        #Remove the file from global list
                                        unset($_FILES[$field][$key]);
                                        continue;
                                    }
                                }
                            }
                        }
                    }
                }
                #Clean up
                if (empty($_FILES[$field])) {
                    unset($_FILES[$field]);
                }
            }
            #Check if any files were left
            if (empty($_FILES)) {
                if (empty($uploadedFiles)) {
                    return (new \http20\Headers)->clientReturn('400', $exit);
                }
            } else {
                #Process files and put them into an array
                foreach ($_FILES as $field=>$files) {
                    #Set destination path
                    if (is_string($destPath)) {
                        $finalPath = $destPath;
                    } else {
                        $finalPath = $destPath[$field];
                    }
                    foreach ($files as $key=>$file) {
                        #Move file, but only if it's not already present in destination
                        if (!is_file($finalPath.'/'.$file['new_name']) && move_uploaded_file($file['tmp_name'], $finalPath.'/'.$file['new_name']) === true) {
                            $uploadedFiles[] = ['server_name' => $file['new_name'], 'user_name' => $file['name'], 'size' => $file['size'], 'type' => $file['type'], 'hash' => $file['hash'], 'field' => $field];
                        } else {
                            if ($intollerant) {
                                return false;
                            }
                        }
                    }
                }
            }
        #Process PUT requests
        } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            if (!isset($_SERVER['CONTENT_LENGTH']) || intval($_SERVER['CONTENT_LENGTH']) === 0) {
                return (new \http20\Headers)->clientReturn('411', $exit);
            }
            $client_size = intval($_SERVER['CONTENT_LENGTH']);
            #Set time limit equal to the size. If load speed is <=10 kilobytes per second - that's definitely low speed session, that we do not want to keep forever
            set_time_limit(intval(floor($client_size/10240)));
            if ($_SERVER['CONTENT_LENGTH'] > $maxUpload) {
                return (new \http20\Headers)->clientReturn('413', $exit);
            }
            #Check that destination is a string
            if (!is_string($destPath)) {
                return (new \http20\Headers)->clientReturn('500', $exit);
            }
            #Attempt to get name from header
            $name = '';
            if (isset($_SERVER['HTTP_CONTENT_DISPOSITION'])) {
                #filename* is preferred over filename as per https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Disposition
                #Note that this format MAY include charset
                $name = preg_replace('/^(.*filename\*=(UTF-8|ISO-8859-1|[!#$%&+\-\^_`{}~a-zA-Z0-9]{1,})\'('.self::langTagRegex.')?\'"?)(?<filename>[^";=\*]*)((("|;).*)|($))$/i', '$12', $_SERVER['HTTP_CONTENT_DISPOSITION']);
                if (empty($name) || $name === $_SERVER['HTTP_CONTENT_DISPOSITION']) {
                    #If we are here, it means that there is no filename*
                    $name = preg_replace('/^(.*filename="?)(?<filename>[^";=\*]*)((("|;).*)|($))$/i', '$2', $_SERVER['HTTP_CONTENT_DISPOSITION']);
                    if (empty($name) || $name === $_SERVER['HTTP_CONTENT_DISPOSITION']) {
                        #If we are here, it means no filename was shared
                        $name = '';
                    }
                }
            }
            #Sanitize the name
            if (!empty($name) && $SafeFileName !== false) {
                $name = basename($SafeFileName->sanitize($name));
            }
            if (empty($name)) {
                #Generate random name. Using 64 to be consistent with sha3-256 hash
                $name = hash('sha3-256', random_bytes(64)).'.put';
                $resumable = false;
            } else {
                $resumable = true;
            }
            #Check if file already exists
            if ($resumable && is_file(sys_get_temp_dir().'/'.$name)) {
                $offset = filesize(sys_get_temp_dir().'/'.$name);
            } else {
                $offset = 0;
            }
            if ($offset !== $client_size) {
                #Open input stream
                $stream = fopen('php://input', 'rb');
                #Check if file was opened
                if ($stream === false) {
                    return (new \http20\Headers)->clientReturn('409', $exit);
                }
                #Read input stream
                if ($offset > 0 && $offset < $client_size) {
                    #We can't fseek php://input, thus we need to read it. To improve peformance we will use php://temp with a memorylimit
                    $garbage = fopen('php://temp', 'wb');
                    #Check if stream was opened
                    if ($garbage === false) {
                        fclose($stream);
                        return (new \http20\Headers)->clientReturn('500', $exit);
                    }
                    $collected = stream_copy_to_stream($stream, $garbage, $offset);
                    #Close stream
                    fclose($garbage);
                    if ($collected != $offset) {
                        #Means we failed to read appropriate amount of bytes
                        fclose($stream);
                        return (new \http20\Headers)->clientReturn('500', $exit);
                    }
                }
                if (feof($stream) === false) {
                    #Open output stream
                    if ($offset < $client_size) {
                        $output = fopen(sys_get_temp_dir().'/'.$name, 'ab');
                    } else {
                        #Means the file is different and we better rewrite it
                        $output = fopen(sys_get_temp_dir().'/'.$name, 'wb');
                    }
                    #Check if stream was opened
                    if ($output === false) {
                        fclose($stream);
                        return (new \http20\Headers)->clientReturn('500', $exit);
                    }
                    #Disable buffering. This should help limiting the memory usage. At least, in some cases.
                    stream_set_read_buffer($stream, 0);
                    stream_set_write_buffer($output, 0);
                    #Ignore user abort to attempt identify when client has aborted
                    #ignore_user_abort(true);
                    #Save file
                    $result = stream_copy_to_stream($stream, $output, $client_size - $offset);
                    #Close streams
                    fclose($stream);
                    fclose($output);
                    #Check that the size is the one we expect
                    if (($result + $offset) < $client_size) {
                        $result = false;
                    } else {
                        $result = true;
                    }
                } else {
                    #Most likely our stream got interrupted during initial read
                    fclose($stream);
                    $result = false;
                }
            } else {
                #Means the file we have is complete
                $result = true;
            }
            if ($result === false) {
                if (!$resumable) {
                    @unlink(sys_get_temp_dir().'/'.$name);
                }
                return (new \http20\Headers)->clientReturn('500', $exit);
            } else {
                #Get file MIME type
                if (isset($_SERVER['CONTENT_TYPE'])) {
                    $filetype = $_SERVER['CONTENT_TYPE'];
                } else {
                    $filetype = 'application/octet-stream';
                }
                if (extension_loaded('fileinfo')) {
                    $filetype = mime_content_type(sys_get_temp_dir().'/'.$name);
                }
                #Get extension of the file
                $ext = array_search($filetype, self::extToMime);
                if ($ext === false) {
                    $ext = 'PUT';
                }
                #Get hash
                $hash = hash_file('sha3-256', sys_get_temp_dir().'/'.$name);
                #Set new name
                $newName = $hash.'.'.$ext;
                #Attempt to move the file
                if (rename(sys_get_temp_dir().'/'.$name, $destPath.'/'.$newName) === false) {
                    return (new \http20\Headers)->clientReturn('500', $exit);
                }
                #Add to array. Using array here for consistency with POST method. Field is reported as PUT to indicate the method. It's advisable not to use it for fields if you use POST method as well
                $uploadedFiles[] = ['server_name' => $newName, 'user_name' => $name, 'size' => $client_size, 'type' => $filetype, 'hash' => $hash, 'field' => 'PUT'];
            }
        }
        if (empty($uploadedFiles)) {
            return (new \http20\Headers)->clientReturn('500', $exit);
        } else {
            if ($exit) {
                #Inform client, that files were uploaded
                return (new \http20\Headers)->clientReturn('200', true);
            } else {
                return $uploadedFiles;
            }
        }
    }
    
    #Function to copy data in small chunks (not HTTP1.1 chunks) based on speed limitation
    public function streamCopy(&$input, &$output, int $totalsize = 0, int $offset = 0, int $speed = 10485760)
    {
        #Ignore user abort to attempt identify when client has aborted
        ignore_user_abort(true);
        #Check that we have resources
        if (!is_resource($input) || !is_resource($output)) {
            return false;
        }
        #Get size if not provided
        if ($totalsize <= 0) {
            $totalsize = fstat($input)['size'];
        }
        #Sanitize speed
        $speed = $this->speedLimit($speed);
        #Set time limit equal to the size. If load speed is <=10 kilobytes per second - that's definitely low speed session, that we do not want to keep forever
        set_time_limit(intval(floor($totalsize/10240)));
        #Set counter for amount of data sent
        $sent = 0;
        while ($sent < $totalsize && connection_status() === CONNECTION_NORMAL) {
            #Using stream_copy_to_stream because it is able to handle much larger files even with relatively large speed limits, close to how readfile() can.
            $sentStat = stream_copy_to_stream($input, $output, $speed, $offset);
            if ($sentStat !== false) {
                $sent += $sentStat;
            } else {
                return false;
            }
            $offset += $speed;
            ob_flush();
            flush();
            #Sleep to limit data rate
            sleep(1);
        }
        if (connection_status() === CONNECTION_NORMAL && $sent >= $totalsize) {
            return $sent;
        } else {
            return false;
        }
    }
    
    #Function to determine speed limit based on maximum allowed memory usage
    public function speedLimit(int $speed = 0, float $percentage = 0.9): int
    {
        #Sanitize percentage
        if ($percentage <= 0 || $percentage > 1.0) {
            $percentage = 0.9;
        }
        #Get memory limit
        $memory = ini_get('memory_limit');
        $memory = $this->phpMemoryToInt($memory);
        #Exclude memory peak usage (assume, that it's either still being used or can be used in near future)
        $memory = $memory - memory_get_peak_usage(true);
        #When using stream there is still a certain memory overhead, so we take only percentage of the memory
        #Percentage was experimentally derived from downloading a 1.5G file with 256M memory limit until there was no "Allowed memory size of X bytes exhausted". Actually it was 0.94, but we would prefer to have at least some headroom.
        $memory = intval(floor($memory * 0.9));
        if ($speed <= 0 || $speed > $memory) {
            $speed = $memory;
        }
        return $speed;
    }
    
    #Function to convert PHP's memory strings (like 256M) used in some settings to integer value (bytes)
    public function phpMemoryToInt(string $memory): int
    {
        #Get suffix
        $suffix = strtolower($memory[strlen($memory)-1]);
        #Get int value
        $memory = intval(substr($memory, 0, -1));
        switch($suffix) {
            case 'g':
                $memory *= 1073741824;
                break;
            case 'm':
                $memory *= 1048576;
                break;
            case 'k':
                $memory *= 1024;
                break;
        }
        return $memory;
    }
    
    #Function to validate HTTP header "Range" and return it as an array. If case of errors it will return array with one element (index 0) equallling false.
    public function rangesValidate(int $size): array
    {
        if (isset($_SERVER['HTTP_RANGE'])) {
            #Validate the value
            if (preg_match('/^bytes=\d*-\d*(\s*,\s*\d*-\d*)*$/i', $_SERVER['HTTP_RANGE']) !== 1) {
                header($_SERVER['SERVER_PROTOCOL'].' 416 Range Not Satisfiable');
                return [0 => false];
            } else {
                #Remove bytes=
                $ranges = preg_replace('/bytes=/i', '', $_SERVER['HTTP_RANGE']);
                #Split ranges
                $ranges = explode(',', $ranges);
                #Sanitize
                foreach ($ranges as $key=>$range) {
                    if (preg_match('/^-\d{1,}$/', $range) === 1) {
                        $ranges[$key] = ['start' => 0, 'end' => intval(ltrim($range, '-'))];
                    } elseif (preg_match('/^\d{1,}-$/', $range) === 1) {
                        $ranges[$key] = ['start' => intval(rtrim($range, '-')), 'end' => ($size - 1)];
                    } elseif (preg_match('/^\d{1,}-\d{1,}$/', $range) === 1) {
                        $temprange = explode('-', $range);
                        $ranges[$key] = ['start' => intval($temprange[0]), 'end' => intval($temprange[1])];
                    } else {
                        #If we get here, something went incredibly wrong, so better exit
                        return [0 => false];
                    }
                    #Check range is of proper value
                    if ($ranges[$key]['start'] >= $ranges[$key]['end'] || $ranges[$key]['start'] >= $size || $ranges[$key]['end'] > $size || ($ranges[0]['end'] - $ranges[0]['start'] + 1) > $size) {
                        return [0 => false];
                    }
                }
                #Checking for overlaps, since as per https://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html we expect non-overlapping ranges
                if (count($ranges) > 1) {
                    foreach ($ranges as $keyPrime=>$rangePrime) {
                        foreach ($ranges as $keySec=>$rangeSec) {
                            #Only compare pairs after current one
                            if ($keySec > $keyPrime) {
                                #If overlap in any way - exit
                                if (
                                    ($rangePrime['start'] === $rangeSec['start'] && $rangePrime['end'] === $rangeSec['end']) ||
                                    ($rangeSec['end'] >= $rangePrime['start'] && $rangeSec['end'] < $rangePrime['end']) ||
                                    ($rangeSec['start'] > $rangePrime['start'] && $rangeSec['start'] <= $rangePrime['end'])
                                ) {
                                    return [0 => false];
                                }
                            }
                        }
                    }
                }
                #If something went wrong and we got an empty range here - return as false
                if (empty($ranges)) {
                    return [0 => false];
                } else {
                    return $ranges;
                }
            }
        } else {
            return [];
        }
    }
}

?>