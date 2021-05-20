<?php
declare(strict_types=1);
namespace Simbiat\http20;

class Common
{
    #Regex for language tag as per https://tools.ietf.org/html/rfc5987 and https://tools.ietf.org/html/rfc5646#section-2.1. Uses portion from https://stackoverflow.com/questions/7035825/regular-expression-for-a-language-tag-as-defined-by-bcp47
    public const langEncRegex = '(UTF-8|ISO-8859-1|[!#$%&+\-\^_`{}~a-zA-Z0-9]{1,})\'((?<grandfathered>(?:en-GB-oed|i-(?:ami|bnn|default|enochian|hak|klingon|lux|mingo|navajo|pwn|t(?:a[oy]|su))|sgn-(?:BE-(?:FR|NL)|CH-DE))|(?:art-lojban|cel-gaulish|no-(?:bok|nyn)|zh-(?:guoyu|hakka|min(?:-nan)?|xiang)))|(?:(?<language>(?:[A-Za-z]{2,3}(?:-(?<extlang>[A-Za-z]{3}(?:-[A-Za-z]{3}){0,2}))?)|[A-Za-z]{4}|[A-Za-z]{5,8})(?:-(?<script>[A-Za-z]{4}))?(?:-(?<region>[A-Za-z]{2}|[0-9]{3}))?(?:-(?<variant>[A-Za-z0-9]{5,8}|[0-9][A-Za-z0-9]{3}))*(?:-(?<extension>[0-9A-WY-Za-wy-z](?:-[A-Za-z0-9]{2,8})+))*)(?:-(?<privateUse>x(?:-[A-Za-z0-9]{1,8})+))?)?\'';
    #Langeauge values as per https://www.ietf.org/rfc/bcp/bcp47.txt (essentially just part of the above value)
    public const langTagRegex = '((?<grandfathered>(?:en-GB-oed|i-(?:ami|bnn|default|enochian|hak|klingon|lux|mingo|navajo|pwn|t(?:a[oy]|su))|sgn-(?:BE-(?:FR|NL)|CH-DE))|(?:art-lojban|cel-gaulish|no-(?:bok|nyn)|zh-(?:guoyu|hakka|min(?:-nan)?|xiang)))|(?:(?<language>(?:[A-Za-z]{2,3}(?:-(?<extlang>[A-Za-z]{3}(?:-[A-Za-z]{3}){0,2}))?)|[A-Za-z]{4}|[A-Za-z]{5,8})(?:-(?<script>[A-Za-z]{4}))?(?:-(?<region>[A-Za-z]{2}|[0-9]{3}))?(?:-(?<variant>[A-Za-z0-9]{5,8}|[0-9][A-Za-z0-9]{3}))*(?:-(?<extension>[0-9A-WY-Za-wy-z](?:-[A-Za-z0-9]{2,8})+))*)(?:-(?<privateUse>x(?:-[A-Za-z0-9]{1,8})+))?)';
    #Regex for MIME type
    public const mimeRegex = '(?<type>application|audio|image|message|multipart|text|video|(x-[-\w.]+))\/[-+\w.]+(?<parameter> *; *[-\w.]+ *= *("*[()<>@,;:\/\\\\\[\]?="\-\w. ]+"|[-\w.]+))*';
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
        'appcache' => 'text/cache-manifest',
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
        'avif' => 'image/avif',
        'avifs' => 'image/avif-sequence',
        'aw' => 'application/applixware',
        'azf' => 'application/vnd.airzip.filesecure.azf',
        'azs' => 'application/vnd.airzip.filesecure.azs',
        'azw' => 'application/vnd.amazon.ebook',
        'bbaw' => 'application/x-bb-appworld',
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
        'crx' => 'application/x-chrome-extension',
        'cryptonote' => 'application/vnd.rig.cryptonote',
        'csh' => 'application/x-csh',
        'csml' => 'chemical/x-csml',
        'csp' => 'application/vnd.commonspace',
        'css' => 'text/css',
        'csv' => 'text/csv',
        'cu' => 'application/cu-seeme',
        'cur' => 'image/x-icon',
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
        'f4a' => 'audio/mp4',
        'f4b' => 'audio/mp4',
        'f4p' => 'video/mp4',
        'f4v' => 'video/mp4',
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
        'geojson' => 'application/geo+json',
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
        'gz' => 'application/gzip',
        'gzip' => 'application/gzip',
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
        'htc' => 'text/x-component',
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
        'jsonld' => 'application/ld+json',
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
        'log' => 'text/plain',
        'lrm' => 'application/vnd.ms-lrm',
        'ltf' => 'application/vnd.frogans.ltf',
        'lvp' => 'audio/vnd.lucent.voice',
        'lwp' => 'application/vnd.lotus-wordpro',
        'm21' => 'application/mp21',
        'm3u' => 'audio/x-mpegurl',
        'm3u8' => 'application/vnd.apple.mpegurl',
        'm4a' => 'audio/mp4',
        'm4v' => 'video/mp4',
        'ma' => 'application/mathematica',
        'mads' => 'application/mads+xml',
        'mag' => 'application/vnd.ecowin.chart',
        'map' => 'application/json',
        'markdown' => 'text/markdown',
        'mathml' => 'application/mathml+xml',
        'mbk' => 'application/vnd.mobius.mbk',
        'mbox' => 'application/mbox',
        'mc1' => 'application/vnd.medcalcdata',
        'mcd' => 'application/vnd.mcd',
        'mcurl' => 'text/vnd.curl.mcurl',
        'md' => 'text/markdown',
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
        'mjs' => 'application/javascript',
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
        'oex' => 'application/x-opera-extension',
        'oga' => 'audio/ogg',
        'ogg' => 'audio/ogg',
        'ogv' => 'video/ogg',
        'ogx' => 'application/ogg',
        'onetoc' => 'application/onenote',
        'opf' => 'application/oebps-package+xml',
        'opus' => 'audio/ogg',
        'org' => 'application/vnd.lotus-organizer',
        'osf' => 'application/vnd.yamaha.openscoreformat',
        'osfpvg' => 'application/vnd.yamaha.openscoreformat.osfpvg+xml',
        'otc' => 'application/vnd.oasis.opendocument.chart-template',
        'otf' => 'font/otf',
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
        'safariextz' => 'application/octet-stream',
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
        'svgz' => 'image/svg+xml',
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
        'tar' => 'application/tar',
        'tar.gz' => 'application/tar+gz',
        'tcap' => 'application/vnd.3gpp2.tcap',
        'tcl' => 'application/x-tcl',
        'teacher' => 'application/vnd.smart.teacher',
        'tei' => 'application/tei+xml',
        'tex' => 'application/x-tex',
        'texinfo' => 'application/x-texinfo',
        'tfi' => 'application/thraud+xml',
        'tfm' => 'application/x-tex-tfm',
        'tgz' => 'application/tar+gz',
        'thmx' => 'application/vnd.ms-officetheme',
        'tiff' => 'image/tiff',
        'tmo' => 'application/vnd.tmobile-livetv',
        'topojson' => 'application/json',
        'torrent' => 'application/x-bittorrent',
        'tpl' => 'application/vnd.groove-tool-template',
        'tpt' => 'application/vnd.trid.tpt',
        'tra' => 'application/vnd.trueapp',
        'trm' => 'application/x-msterminal',
        'tsd' => 'application/timestamped-data',
        'tsv' => 'text/tab-separated-values',
        'ttc' => 'font/collection',
        'ttf' => 'font/ttf',
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
        'vcard' => 'text/vcard',
        'vcs' => 'text/x-vcalendar',
        'vcx' => 'application/vnd.vcx',
        'vis' => 'application/vnd.visionary',
        'viv' => 'video/vnd.vivo',
        'vsd' => 'application/vnd.visio',
        'vsdx' => 'application/vnd.visio2013',
        'vsf' => 'text/vcard',
        'vtt' => 'text/vtt',
        'vtu' => 'model/vnd.vtu',
        'vxml' => 'application/voicexml+xml',
        'wad' => 'application/x-doom',
        'wasm' => 'application/wasm',
        'wav' => 'audio/x-wav',
        'wax' => 'audio/x-ms-wax',
        'wbmp' => 'image/vnd.wap.wbmp',
        'wbs' => 'application/vnd.criticaltools.wbs+xml',
        'wbxml' => 'application/vnd.wap.wbxml',
        'weba' => 'audio/webm',
        'webapp' => 'application/x-web-app-manifest+json',
        'webm' => 'video/webm',
        'webmanifest' => 'application/manifest+json',
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
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
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
        'xloc' => 'text/vnd.rim.location.xloc',
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
    
    #Wrapper for date(), that handles strings and allows validation of result
    public function valueToTime(string|int|float|null $time, string $format, string $validregex = ''): string
    {
        #If we want to use a constant, but it was sent as a string
        if (str_starts_with(strtoupper($format), 'DATE_')) {
            $format = constant($format);
        }
        if (empty($time)) {
            $time = date($format, time());
        } else {
            if (is_numeric($time)) {
                #Ensure we use int
                $time = date($format, intval($time));
            } else {
                if (is_string($time)) {
                    #Attempt to convert string to time
                    $time = date($format, strtotime($time));
                } else {
                    throw new \UnexpectedValueException('Time provided to `valueToTime` is neither numeric or string');
                }
            }
        }
        if ($format === 'c' || $format === \DATE_ATOM) {
            $validregex = '/^(?:[1-9]\d{3}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1\d|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[1-9]\d(?:0[48]|[2468][048]|[13579][26])|(?:[2468][048]|[13579][26])00)-02-29)T(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d(?:Z|[+-][01]\d:[0-5]\d)$/i';
        }
        if (!empty($validregex) && preg_match($validregex, $time) !== 1) {
            throw new \UnexpectedValueException('Date provided to `feedIDGen` failed to be validated against the provided regex');
        }
        return $time;
    }
    
    #Function to prepare ID for Atom feed as suggested on http://web.archive.org/web/20110514113830/http://diveintomark.org/archives/2004/05/28/howto-atom-id
    public function atomIDGen(string $link, string|int|float|null $date = NULL): string
    {
        $date = $this->valueToTime($date, 'Y-m-d', '/^\d{4}-\d{2}-\d{2}$/i');
        #Remove URI protocol (if any)
        $link = preg_replace('/^(?:[a-zA-Z]+?:\/\/)?/im', '', $this->htmlToRFC3986($link));
        #Replace any # with /
        $link = preg_replace('/#/im', '/', $link);
        #Remove HTML/XML reserved characters as precaution
        $link = preg_replace('/[\\\'"<>&]/im', '', $link);
        #Add 'tag:' to beginning and ',Y-m-d:' after domain name
        $link = preg_replace('/(?<domain>^(?:www\.)?([^:\/\n?]+))(?<rest>.*)/im', 'tag:$1,'.$date.':$3', $link);
        return $link;
    }
    
    #Function utilizes ob functions to attempt compresing output sent to browser and also provide browser with length of the output and some caching-related headers
    public function zEcho(string $string, string $cacheStrat = ''): void
    {
        #Close session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        (new \Simbiat\http20\Headers)->cacheControl($string, $cacheStrat, true);
        #Check that zlib is loaded and client supports GZip. We are ignoring Deflate because of known inconsistences with how it is handled by browsers depending on whether it is wrapped in Zlib or not.
        if (extension_loaded('zlib') && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
            #It is recommended to use ob_gzhandler or zlib.output_compression, but I am getting inconsistent results with headers when using them, thus this "direct" approach.
            #GZipping the string
            $string = gzcompress($string, 9, FORCE_GZIP);
            #Send header with format
            header('Content-Encoding: gzip');
            #Send header with length
            header('Content-Length: '.strlen($string));
        } else {
            #Send header with length
            header('Content-Length: '.strlen($string));
        }
        #Exit if HEAD method was used (by this time all headers should have been sent
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'HEAD') {
            exit;
        }
        #Send the output
        echo $string;
        exit;
    }
    
    #Function to check if string is a mail address as per RFC 5322
    public function emailValidator(string $string): bool
    {
        if (preg_match('/^(?:[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9]))\.){3}(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9])|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])$/i', $string) === 1) {
            return true;
        } else {
            return false;
        }
    }
    
    #Function to check if string is an URI as per RFC 3986
    public function uriValidator(string $string): bool
    {
        if (preg_match('/^(?<scheme>[a-z][a-z0-9+.-]+):(?<authority>\/\/(?<user>[^@]+@)?(?<host>[\p{L}0-9.\-_~]+)(?<port>:\d+)?)?(?<path>(?:[\p{L}0-9-._~]|%[a-f0-9]{2}|[!$&\'()*+,;=:@])+(?:\/(?:[\p{L}0-9-._~]|%[a-f0-9]{2}|[!$&\'()*+,;=:@])*)*|(?:\/(?:[\p{L}0-9-._~]|%[a-f0-9]{2}|[!$&\'()*+,;=:@])+)*)?(?<query>\?(?:[\p{L}0-9-._~]|%[a-f0-9]{2}|[!$&\'()*+,;=:@]|[\/?])+)?(?<fragment>\#(?:[\p{L}0-9-._~]|%[a-f0-9]{2}|[!$&\'()*+,;=:@]|[\/?])+)?$/iu', $this->htmlToRFC3986($string)) === 1) {
            return true;
        } else {
            return false;
        }
    }
    
    #Function to check if string is a valid language code
    public function LangCodeCheck(string $string): bool
    {
        if (in_array(strtolower($string),
            ['af', 'sq', 'eu', 'be', 'bg', 'ca', 'zh-cn', 'zh-tw', 'hr', 'cs', 'da', 'nl', 'nl-be', 'nl-nl', 'en', 'en-au', 'en-bz', 'en-ca', 'en-ie', 'en-jm', 'en-nz', 'en-ph', 'en-za', 'en-tt', 'en-gb', 'en-us', 'en-zw', 'et', 'fo', 'fi', 'fr', 'fr-be', 'fr-ca', 'fr-fr', 'fr-lu', 'fr-mc', 'fr-ch', 'gl', 'gd', 'de', 'de-at', 'de-de', 'de-li', 'de-lu', 'de-ch', 'el', 'haw', 'hu', 'is', 'in', 'ga', 'it', 'it-it', 'it-ch', 'ja', 'ko', 'mk', 'no', 'pl', 'pt', 'pt-br', 'pt-pt', 'ro', 'ro-mo', 'ro-ro', 'ru', 'ru-mo', 'ru-ru', 'sr', 'sk', 'sl', 'es', 'es-ar', 'es-bo', 'es-cl', 'es-co', 'es-cr', 'es-do', 'es-ec', 'es-sv', 'es-gt', 'es-hn', 'es-mx', 'es-ni', 'es-pa', 'es-py', 'es-pe', 'es-pr', 'es-es', 'es-uy', 'es-ve', 'sv', 'sv-fi', 'sv-se', 'tr', 'uk']
            )) {
            return true;
        } else {
            return false;
        }
    }
    
    #Function does the same as rawurlencode but only for selected characters, that are restricted in HTML/XML. Useful for URIs that can have these characters and need to be used in HTML/XML and thus can't use htmlentities, but otherwise break HTML/XML
    #$full means that all of them will be converted (useful when text inside a tag). If `false` only < and & are converted (useful when inside attribute). If `false` is used - be careful with quotes inside the string you provide, because they can invalidate your HTML/XML
    public function htmlToRFC3986(string $string, bool $full = true): string
    {
        if ($full) {
            return str_replace(['\'', '"', '&', '<', '>'], ['%27', '%22', '%26', '%3C', '%3E'], $string);
        } else {
            return str_replace(['&', '<'], ['%26', '%3C'], $string);
        }
    }
    
    #Function to merge CSS/JS files to reduce number of connections to your server, yet allow you to keep the files separate for easier development. It also allows you to minify the result for extra size saving, but be careful with that.
    #Minification is based on https://gist.github.com/Rodrigo54/93169db48194d470188f
    public function reductor(string|array $files, string $type, bool $minify = false, string $tofile = '', string $cacheStrat = ''): void
    {
        #Set content to empty string as precaution
        $content = '';
        #Check if empty value was sent
        if (empty($files)) {
            throw new \UnexpectedValueException('Empty set of files provided to `reductor` function');
        }
        #Check if a string
        if (is_string($files)) {
            #Convert to array
           $files = [$files];
        }
        #Prepare array of dates
        $dates = [];
        #Iterate array
        foreach ($files as $file) {
            #Check if string is file
            if (is_file($file)) {
                #Check extension
                if (strcasecmp(pathinfo($file)['extension'], $type) === 0) {
                    #Add date to list
                    $dates[] = filemtime($file);
                    #Add contents
                    $content .= file_get_contents($file);
                }
            } else {
                #Check if directory
                if (is_dir($file)) {
                    $filelist = (new \RecursiveIteratorIterator((new \RecursiveDirectoryIterator($file, \FilesystemIterator::FOLLOW_SYMLINKS | \FilesystemIterator::SKIP_DOTS)), \RecursiveIteratorIterator::SELF_FIRST));
                    foreach ($filelist as $subfile) {
                        if (strcasecmp($subfile->getExtension(), $type) === 0) {
                            #Add date to list
                            $dates[] = $subfile->getMTime();
                            #Add contents
                            $content .= file_get_contents($subfile->getRealPath());
                        }
                    }
                }
            }
        }
        #Get date if we are directly outputting the data
        if (empty($tofile)) {
            #Send Last Modifed header and exit, if we hit browser cache
            (new \Simbiat\http20\Headers)->lastModified(max($dates), true);
        }
        #Minify
        if ($minify === true) {
            switch (strtolower($type)) {
                case 'js':
                    $content = preg_replace(
                        [
                            // Remove comment(s)
                            '#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',
                            // Remove white-space(s) outside the string and regex
                            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',
                            // Remove the last semicolon
                            '#;+\}#',
                            // Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`
                            '#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',
                            // --ibid. From `foo['bar']` to `foo.bar`
                            '#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i'
                        ],
                        [
                            '$1',
                            '$1$2',
                            '}',
                            '$1$3',
                            '$1.$3'
                        ],
                        $content);
                    break;
                case 'css':
                    $content = preg_replace(
                        [
                            // Remove comment(s)
                            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
                            // Remove unused white-space(s)
                            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~]|\s(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
                            // Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
                            '#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
                            // Replace `:0 0 0 0` with `:0`
                            '#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
                            // Replace `background-position:0` with `background-position:0 0`
                            '#(background-position):0(?=[;\}])#si',
                            // Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space
                            '#(?<=[\s:,\-])0+\.(\d+)#s',
                            // Minify string value
                            '#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
                            '#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
                            // Minify HEX color code
                            '#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
                            // Replace `(border|outline):none` with `(border|outline):0`
                            '#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
                            // Remove empty selector(s)
                            '#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s'
                        ],
                        [
                            '$1',
                            '$1$2$3$4$5$6$7',
                            '$1',
                            ':0',
                            '$1:0 0',
                            '.$1',
                            '$1$3',
                            '$1$2$4$5',
                            '$1$2$3',
                            '$1:0',
                            '$1$2'
                        ],
                        $content);
                    break;
                case 'html':
                    $content = preg_replace_callback('#<([^\/\s<>!]+)(?:\s+([^<>]*?)\s*|\s*)(\/?)>#s',
                        function($matches) {
                            return '<' . $matches[1] . preg_replace('#([^\s=]+)(\=([\'"]?)(.*?)\3)?(\s+|$)#s', ' $1$2', $matches[2]) . $matches[3] . '>';
                        }, str_replace("\r", '', $content));
                    $content = preg_replace(
                        [
                            // t = text
                            // o = tag open
                            // c = tag close
                            // Keep important white-space(s) after self-closing HTML tag(s)
                            '#<(img|input)(>| .*?>)#s',
                            // Remove a line break and two or more white-space(s) between tag(s)
                            '#(<!--.*?-->)|(>)(?:\n*|\s{2,})(<)|^\s*|\s*$#s',
                            '#(<!--.*?-->)|(?<!\>)\s+(<\/.*?>)|(<[^\/]*?>)\s+(?!\<)#s', // t+c || o+t
                            '#(<!--.*?-->)|(<[^\/]*?>)\s+(<[^\/]*?>)|(<\/.*?>)\s+(<\/.*?>)#s', // o+o || c+c
                            '#(<!--.*?-->)|(<\/.*?>)\s+(\s)(?!\<)|(?<!\>)\s+(\s)(<[^\/]*?\/?>)|(<[^\/]*?\/?>)\s+(\s)(?!\<)#s', // c+t || t+o || o+t -- separated by long white-space(s)
                            '#(<!--.*?-->)|(<[^\/]*?>)\s+(<\/.*?>)#s', // empty tag
                            '#<(img|input)(>| .*?>)<\/\1>#s', // reset previous fix
                            '#(&nbsp;)&nbsp;(?![<\s])#', // clean up ...
                            '#(?<=\>)(&nbsp;)(?=\<)#', // --ibid
                            // Remove HTML comment(s) except IE comment(s)
                            '#\s*<!--(?!\[if\s).*?-->\s*|(?<!\>)\n+(?=\<[^!])#s'
                        ],
                        [
                            '<$1$2</$1>',
                            '$1$2$3',
                            '$1$2$3',
                            '$1$2$3$4$5',
                            '$1$2$3$4$5$6$7',
                            '$1$2$3',
                            '<$1$2',
                            '$1 ',
                            '$1',
                            ''
                        ],
                        $content);
                    break;
            }
        }
        if (empty($tofile)) {
            #Send appropriate header
            switch (strtolower($type)) {
                case 'js':
                    header('Content-Type: application/javascript; charset=utf-8');
                    break;
                case 'css':
                    header('Content-Type: text/css; charset=utf-8');
                    break;
                default:
                    header('Content-Type: text/html; charset=utf-8');
                    break;
            }
            #Send data to browser
            $this->zEcho($content, $cacheStrat);
        } else {
            file_put_contents($tofile, $content);
        }
    }
    
    #Function to force close HTTP connection
    public function forceClose(): void
    {
        #Close session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        #Send header to notify, that connection was closed
        @header('Connection: close');
        #Clean output buffer and close it
        ob_end_clean();
        #Clean system buffer
        flush();
        exit;
    }
}
?>