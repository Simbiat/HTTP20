<?php
declare(strict_types = 1);

namespace Simbiat\http20;

use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\NoReturn;
use function in_array, is_string, extension_loaded;

/**
 * Collection of useful HTTP related functions
 */
class Common
{
    /**
     * Regex for language tag as per https://tools.ietf.org/html/rfc5987 and https://tools.ietf.org/html/rfc5646#section-2.1. Uses a portion from https://stackoverflow.com/questions/7035825/regular-expression-for-a-language-tag-as-defined-by-bcp47
     * @var string
     */
    public const string LANGUAGE_ENC_REGEX = /** @lang PhpRegExp */
        '(UTF-8|ISO-8859-1|[!#$%&+\-^_`{}~a-zA-Z0-9]+)\'((?<grandfathered>(?:en-GB-oed|i-(?:ami|bnn|default|enochian|hak|klingon|lux|mingo|navajo|pwn|t(?:a[oy]|su))|sgn-(?:BE-(?:FR|NL)|CH-DE))|(?:art-lojban|cel-gaulish|no-(?:bok|nyn)|zh-(?:guoyu|hakka|min(?:-nan)?|xiang)))|(?<language>[A-Za-z]{2,3}(?:-(?<extlang>[A-Za-z]{3}(?:-[A-Za-z]{3}){0,2}))?|[A-Za-z]{4}|[A-Za-z]{5,8})(?:-(?<script>[A-Za-z]{4}))?(?:-(?<region>[A-Za-z]{2}|[0-9]{3}))?(?:-(?<variant>[A-Za-z0-9]{5,8}|[0-9][A-Za-z0-9]{3}))*(?:-(?<extension>[0-9A-WY-Za-wy-z](?:-[A-Za-z0-9]{2,8})+))*(?:-(?<privateUse>x(?:-[A-Za-z0-9]{1,8})+))?)?\'';
    /**
     * Language values as per https://www.ietf.org/rfc/bcp/bcp47.txt (essentially just part of the above value)
     * @var string
     */
    public const string LANGUAGE_TAG_REGEX = /** @lang PhpRegExp */
        '((?<grandfathered>(?:en-GB-oed|i-(?:ami|bnn|default|enochian|hak|klingon|lux|mingo|navajo|pwn|t(?:a[oy]|su))|sgn-(?:BE-(?:FR|NL)|CH-DE))|(?:art-lojban|cel-gaulish|no-(?:bok|nyn)|zh-(?:guoyu|hakka|min(?:-nan)?|xiang)))|(?<language>[A-Za-z]{2,3}(?:-(?<extlang>[A-Za-z]{3}(?:-[A-Za-z]{3}){0,2}))?|[A-Za-z]{4}|[A-Za-z]{5,8})(?:-(?<script>[A-Za-z]{4}))?(?:-(?<region>[A-Za-z]{2}|[0-9]{3}))?(?:-(?<variant>[A-Za-z0-9]{5,8}|[0-9][A-Za-z0-9]{3}))*(?:-(?<extension>[0-9A-WY-Za-wy-z](?:-[A-Za-z0-9]{2,8})+))*(?:-(?<privateUse>x(?:-[A-Za-z0-9]{1,8})+))?)';
    /**
     * Regex for MIME type
     * @var string
     */
    public const string MIME_REGEX = /** @lang PhpRegExp */
        '(?<type>application|audio|image|message|multipart|text|video|(x-[-\w.]+))\/[-+\w.]+(?<parameter> *; *[-\w.]+ *= *("*[()<>@,;:\/\\\\\[\]?="\-\w. ]+"|[-\w.]+))*';
    /**
     * Linkage of extensions to MIME types
     * @var array
     */
    public const array EXTENSION_TO_MIME = [
        'ez' => 'application/andrew-inset',
        'aw' => 'application/applixware',
        'atom' => 'application/atom+xml',
        'atomcat' => 'application/atomcat+xml',
        'atomsvc' => 'application/atomsvc+xml',
        'ccxml' => 'application/ccxml+xml',
        'cdmia' => 'application/cdmi-capability',
        'cdmic' => 'application/cdmi-container',
        'cdmid' => 'application/cdmi-domain',
        'cdmio' => 'application/cdmi-object',
        'cdmiq' => 'application/cdmi-queue',
        'z' => 'application/compress',
        'cu' => 'application/cu-seeme',
        'davmount' => 'application/davmount+xml',
        'dssc' => 'application/dssc+der',
        'xdssc' => 'application/dssc+xml',
        'es' => 'application/ecmascript',
        'emma' => 'application/emma+xml',
        'epub' => 'application/epub+zip',
        'exi' => 'application/exi',
        'pfr' => 'application/font-tdpfr',
        'woff4' => 'application/font-woff2',
        'geojson' => 'application/geo+json',
        'gpx' => 'application/gpx+xml',
        'gz' => 'application/gzip',
        'gzip' => 'application/gzip',
        'stk' => 'application/hyperstudio',
        'ipfix' => 'application/ipfix',
        'ear' => 'application/java-archive',
        'jar' => 'application/java-archive',
        'war' => 'application/java-archive',
        'ser' => 'application/java-serialized-object',
        'class' => 'application/java-vm',
        'json' => 'application/json',
        'map' => 'application/json',
        'topojson' => 'application/json',
        'jsonld' => 'application/ld+json',
        'hqx' => 'application/mac-binhex40',
        'cpt' => 'application/mac-compactpro',
        'mads' => 'application/mads+xml',
        'webmanifest' => 'application/manifest+json',
        'mrc' => 'application/marc',
        'mrcx' => 'application/marcxml+xml',
        'ma' => 'application/mathematica',
        'mathml' => 'application/mathml+xml',
        'mbox' => 'application/mbox',
        'mscml' => 'application/mediaservercontrol+xml',
        'meta4' => 'application/metalink4+xml',
        'mets' => 'application/mets+xml',
        'mods' => 'application/mods+xml',
        'm21' => 'application/mp21',
        'doc' => 'application/msword',
        'mxf' => 'application/mxf',
        'bin' => 'application/octet-stream',
        'dll' => 'application/octet-stream',
        'dms' => 'application/octet-stream',
        'img' => 'application/octet-stream',
        'lza' => 'application/octet-stream',
        'lzh' => 'application/octet-stream',
        'msi' => 'application/octet-stream',
        'msm' => 'application/octet-stream',
        'msp' => 'application/octet-stream',
        'safariextz' => 'application/octet-stream',
        'oda' => 'application/oda',
        'opf' => 'application/oebps-package+xml',
        'ogx' => 'application/ogg',
        'onetoc' => 'application/onenote',
        'xer' => 'application/patch-ops-error+xml',
        'pdf' => 'application/pdf',
        'pgp' => 'application/pgp-encrypted',
        'prf' => 'application/pics-rules',
        'p10' => 'application/pkcs10',
        'p7m' => 'application/pkcs7-mime',
        'p7s' => 'application/pkcs7-signature',
        'p8' => 'application/pkcs8',
        'ac' => 'application/pkix-attr-cert',
        'cer' => 'application/pkix-cert',
        'pki' => 'application/pkixcmp',
        'crl' => 'application/pkix-crl',
        'pkipath' => 'application/pkix-pkipath',
        'pls' => 'application/pls+xml',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',
        'cww' => 'application/prs.cww',
        'pskcxml' => 'application/pskc+xml',
        'rdf' => 'application/rdf+xml',
        'rif' => 'application/reginfo+xml',
        'rnc' => 'application/relax-ng-compact-syntax',
        'rl' => 'application/resource-lists+xml',
        'rld' => 'application/resource-lists-diff+xml',
        'rs' => 'application/rls-services+xml',
        'rsd' => 'application/rsd+xml',
        'rss' => 'application/rss+xml',
        'rtf' => 'application/rtf',
        'sbml' => 'application/sbml+xml',
        'scq' => 'application/scvp-cv-request',
        'scs' => 'application/scvp-cv-response',
        'spq' => 'application/scvp-vp-request',
        'spp' => 'application/scvp-vp-response',
        'sdp' => 'application/sdp',
        'setpay' => 'application/set-payment-initiation',
        'setreg' => 'application/set-registration-initiation',
        'shf' => 'application/shf+xml',
        'smi' => 'application/smil+xml',
        'smil' => 'application/smil+xml',
        'rq' => 'application/sparql-query',
        'srx' => 'application/sparql-results+xml',
        'gram' => 'application/srgs',
        'grxml' => 'application/srgs+xml',
        'sru' => 'application/sru+xml',
        'ssml' => 'application/ssml+xml',
        'tar.gz' => 'application/tar+gz',
        'tgz' => 'application/tar+gz',
        'tei' => 'application/tei+xml',
        'tfi' => 'application/thraud+xml',
        'tsd' => 'application/timestamped-data',
        'plb' => 'application/vnd.3gpp.pic-bw-large',
        'psb' => 'application/vnd.3gpp.pic-bw-small',
        'pvb' => 'application/vnd.3gpp.pic-bw-var',
        'tcap' => 'application/vnd.3gpp2.tcap',
        'pwn' => 'application/vnd.3m.post-it-notes',
        'aso' => 'application/vnd.accpac.simply.aso',
        'imp' => 'application/vnd.accpac.simply.imp',
        'acu' => 'application/vnd.acucobol',
        'atc' => 'application/vnd.acucorp',
        'air' => 'application/vnd.adobe.air-application-installer-package+zip',
        'fxp' => 'application/vnd.adobe.fxp',
        'xdp' => 'application/vnd.adobe.xdp+xml',
        'xfdf' => 'application/vnd.adobe.xfdf',
        'ahead' => 'application/vnd.ahead.space',
        'azf' => 'application/vnd.airzip.filesecure.azf',
        'azs' => 'application/vnd.airzip.filesecure.azs',
        'azw' => 'application/vnd.amazon.ebook',
        'acc' => 'application/vnd.americandynamics.acc',
        'ami' => 'application/vnd.amiga.ami',
        'apk' => 'application/vnd.android.package-archive',
        'cii' => 'application/vnd.anser-web-certificate-issue-initiation',
        'fti' => 'application/vnd.anser-web-funds-transfer-initiation',
        'atx' => 'application/vnd.antix.game-component',
        'mpkg' => 'application/vnd.apple.installer+xml',
        'm3u8' => 'application/vnd.apple.mpegurl',
        'swi' => 'application/vnd.aristanetworks.swi',
        'aep' => 'application/vnd.audiograph',
        'mpm' => 'application/vnd.blueice.multipass',
        'bmi' => 'application/vnd.bmi',
        'rep' => 'application/vnd.businessobjects',
        'cdxml' => 'application/vnd.chemdraw+xml',
        'mmd' => 'application/vnd.chipnuts.karaoke-mmd',
        'cdy' => 'application/vnd.cinderella',
        'cla' => 'application/vnd.claymore',
        'rp9' => 'application/vnd.cloanto.rp9',
        'c4g' => 'application/vnd.clonk.c4group',
        'c11amc' => 'application/vnd.cluetrust.cartomobile-config',
        'c11amz' => 'application/vnd.cluetrust.cartomobile-config-pkg',
        'csp' => 'application/vnd.commonspace',
        'cdbcmsg' => 'application/vnd.contact.cmsg',
        'cmc' => 'application/vnd.cosmocaller',
        'clkx' => 'application/vnd.crick.clicker',
        'clkk' => 'application/vnd.crick.clicker.keyboard',
        'clkp' => 'application/vnd.crick.clicker.palette',
        'clkt' => 'application/vnd.crick.clicker.template',
        'clkw' => 'application/vnd.crick.clicker.wordbank',
        'wbs' => 'application/vnd.criticaltools.wbs+xml',
        'pml' => 'application/vnd.ctc-posml',
        'ppd' => 'application/vnd.cups-ppd',
        'car' => 'application/vnd.curl.car',
        'pcurl' => 'application/vnd.curl.pcurl',
        'rdz' => 'application/vnd.data-vision.rdz',
        'fe_launch' => 'application/vnd.denovo.fcselayout-link',
        'dna' => 'application/vnd.dna',
        'mlp' => 'application/vnd.dolby.mlp',
        'dpg' => 'application/vnd.dpgraph',
        'dfac' => 'application/vnd.dreamfactory',
        'ait' => 'application/vnd.dvb.ait',
        'svc' => 'application/vnd.dvb.service',
        'geo' => 'application/vnd.dynageo',
        'mag' => 'application/vnd.ecowin.chart',
        'nml' => 'application/vnd.enliven',
        'esf' => 'application/vnd.epson.esf',
        'msf' => 'application/vnd.epson.msf',
        'qam' => 'application/vnd.epson.quickanime',
        'slt' => 'application/vnd.epson.salt',
        'ssf' => 'application/vnd.epson.ssf',
        'es3' => 'application/vnd.eszigno3+xml',
        'ez2' => 'application/vnd.ezpix-album',
        'ez3' => 'application/vnd.ezpix-package',
        'fdf' => 'application/vnd.fdf',
        'seed' => 'application/vnd.fdsn.seed',
        'gph' => 'application/vnd.flographit',
        'ftc' => 'application/vnd.fluxtime.clip',
        'fm' => 'application/vnd.framemaker',
        'fnc' => 'application/vnd.frogans.fnc',
        'ltf' => 'application/vnd.frogans.ltf',
        'fsc' => 'application/vnd.fsc.weblaunch',
        'oas' => 'application/vnd.fujitsu.oasys',
        'oa2' => 'application/vnd.fujitsu.oasys2',
        'oa3' => 'application/vnd.fujitsu.oasys3',
        'fg5' => 'application/vnd.fujitsu.oasysgp',
        'bh2' => 'application/vnd.fujitsu.oasysprs',
        'ddd' => 'application/vnd.fujixerox.ddd',
        'xdw' => 'application/vnd.fujixerox.docuworks',
        'xbd' => 'application/vnd.fujixerox.docuworks.binder',
        'fzs' => 'application/vnd.fuzzysheet',
        'txd' => 'application/vnd.genomatix.tuxedo',
        'ggb' => 'application/vnd.geogebra.file',
        'ggt' => 'application/vnd.geogebra.tool',
        'gex' => 'application/vnd.geometry-explorer',
        'gxt' => 'application/vnd.geonext',
        'g2w' => 'application/vnd.geoplan',
        'g3w' => 'application/vnd.geospace',
        'gmx' => 'application/vnd.gmx',
        'kml' => 'application/vnd.google-earth.kml+xml',
        'kmz' => 'application/vnd.google-earth.kmz',
        'gqf' => 'application/vnd.grafeq',
        'gac' => 'application/vnd.groove-account',
        'ghf' => 'application/vnd.groove-help',
        'gim' => 'application/vnd.groove-identity-message',
        'grv' => 'application/vnd.groove-injector',
        'gtm' => 'application/vnd.groove-tool-message',
        'tpl' => 'application/vnd.groove-tool-template',
        'vcg' => 'application/vnd.groove-vcard',
        'hal' => 'application/vnd.hal+xml',
        'zmm' => 'application/vnd.handheld-entertainment+xml',
        'hbci' => 'application/vnd.hbci',
        'les' => 'application/vnd.hhe.lesson-player',
        'hpgl' => 'application/vnd.hp-hpgl',
        'hpid' => 'application/vnd.hp-hpid',
        'hps' => 'application/vnd.hp-hps',
        'jlt' => 'application/vnd.hp-jlyt',
        'pcl' => 'application/vnd.hp-pcl',
        'pclxl' => 'application/vnd.hp-pclxl',
        'sfd-hdstx' => 'application/vnd.hydrostatix.sof-data',
        'x3d' => 'application/vnd.hzn-3d-crossword',
        'mpy' => 'application/vnd.ibm.minipay',
        'afp' => 'application/vnd.ibm.modcap',
        'irm' => 'application/vnd.ibm.rights-management',
        'sc' => 'application/vnd.ibm.secure-container',
        'icc' => 'application/vnd.iccprofile',
        'igl' => 'application/vnd.igloader',
        'ivp' => 'application/vnd.immervision-ivp',
        'ivu' => 'application/vnd.immervision-ivu',
        'igm' => 'application/vnd.insors.igm',
        'xpw' => 'application/vnd.intercon.formnet',
        'i2g' => 'application/vnd.intergeo',
        'qbo' => 'application/vnd.intu.qbo',
        'qfx' => 'application/vnd.intu.qfx',
        'rcprofile' => 'application/vnd.ipunplugged.rcprofile',
        'irp' => 'application/vnd.irepository.package+xml',
        'fcs' => 'application/vnd.isac.fcs',
        'xpr' => 'application/vnd.is-xpr',
        'jam' => 'application/vnd.jam',
        'rms' => 'application/vnd.jcp.javame.midlet-rms',
        'jisp' => 'application/vnd.jisp',
        'joda' => 'application/vnd.joost.joda-archive',
        'ktz' => 'application/vnd.kahootz',
        'karbon' => 'application/vnd.kde.karbon',
        'chrt' => 'application/vnd.kde.kchart',
        'kfo' => 'application/vnd.kde.kformula',
        'flw' => 'application/vnd.kde.kivio',
        'kon' => 'application/vnd.kde.kontour',
        'kpr' => 'application/vnd.kde.kpresenter',
        'ksp' => 'application/vnd.kde.kspread',
        'kwd' => 'application/vnd.kde.kword',
        'htke' => 'application/vnd.kenameaapp',
        'kia' => 'application/vnd.kidspiration',
        'kne' => 'application/vnd.kinar',
        'skd' => 'application/vnd.koan',
        'skm' => 'application/vnd.koan',
        'skp' => 'application/vnd.koan',
        'skt' => 'application/vnd.koan',
        'sse' => 'application/vnd.kodak-descriptor',
        'lasxml' => 'application/vnd.las.las+xml',
        'lbd' => 'application/vnd.llamagraphics.life-balance.desktop',
        'lbe' => 'application/vnd.llamagraphics.life-balance.exchange+xml',
        '123' => 'application/vnd.lotus-1-2-3',
        'apr' => 'application/vnd.lotus-approach',
        'pre' => 'application/vnd.lotus-freelance',
        'nsf' => 'application/vnd.lotus-notes',
        'org' => 'application/vnd.lotus-organizer',
        'scm' => 'application/vnd.lotus-screencam',
        'lwp' => 'application/vnd.lotus-wordpro',
        'portpkg' => 'application/vnd.macports.portpkg',
        'mcd' => 'application/vnd.mcd',
        'mc1' => 'application/vnd.medcalcdata',
        'cdkey' => 'application/vnd.mediastation.cdkey',
        'mwf' => 'application/vnd.mfer',
        'mfm' => 'application/vnd.mfmp',
        'flo' => 'application/vnd.micrografx.flo',
        'igx' => 'application/vnd.micrografx.igx',
        'mif' => 'application/vnd.mif',
        'daf' => 'application/vnd.mobius.daf',
        'dis' => 'application/vnd.mobius.dis',
        'mbk' => 'application/vnd.mobius.mbk',
        'mqy' => 'application/vnd.mobius.mqy',
        'msl' => 'application/vnd.mobius.msl',
        'plc' => 'application/vnd.mobius.plc',
        'txf' => 'application/vnd.mobius.txf',
        'mpn' => 'application/vnd.mophun.application',
        'mpc' => 'application/vnd.mophun.certificate',
        'xul' => 'application/vnd.mozilla.xul+xml',
        'mdb' => 'application/vnd.ms-access',
        'cil' => 'application/vnd.ms-artgalry',
        'cab' => 'application/vnd.ms-cab-compressed',
        'mseq' => 'application/vnd.mseq',
        'xls' => 'application/vnd.ms-excel',
        'xlam' => 'application/vnd.ms-excel.addin.macroenabled.12',
        'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroenabled.12',
        'xlsm' => 'application/vnd.ms-excel.sheet.macroenabled.12',
        'xltm' => 'application/vnd.ms-excel.template.macroenabled.12',
        'eot' => 'application/vnd.ms-fontobject',
        'chm' => 'application/vnd.ms-htmlhelp',
        'ims' => 'application/vnd.ms-ims',
        'lrm' => 'application/vnd.ms-lrm',
        'thmx' => 'application/vnd.ms-officetheme',
        'cat' => 'application/vnd.ms-pki.seccat',
        'stl' => 'application/vnd.ms-pki.stl',
        'ppt' => 'application/vnd.ms-powerpoint',
        'ppam' => 'application/vnd.ms-powerpoint.addin.macroenabled.12',
        'pptm' => 'application/vnd.ms-powerpoint.presentation.macroenabled.12',
        'sldm' => 'application/vnd.ms-powerpoint.slide.macroenabled.12',
        'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroenabled.12',
        'potm' => 'application/vnd.ms-powerpoint.template.macroenabled.12',
        'mpp' => 'application/vnd.ms-project',
        'docm' => 'application/vnd.ms-word.document.macroenabled.12',
        'dotm' => 'application/vnd.ms-word.template.macroenabled.12',
        'wps' => 'application/vnd.ms-works',
        'wpl' => 'application/vnd.ms-wpl',
        'wri' => 'application/vnd.ms-write',
        'xps' => 'application/vnd.ms-xpsdocument',
        'mus' => 'application/vnd.musician',
        'msty' => 'application/vnd.muvee.style',
        'nlu' => 'application/vnd.neurolanguage.nlu',
        'nnd' => 'application/vnd.noblenet-directory',
        'nns' => 'application/vnd.noblenet-sealer',
        'nnw' => 'application/vnd.noblenet-web',
        'ngdat' => 'application/vnd.nokia.n-gage.data',
        'n-gage' => 'application/vnd.nokia.n-gage.symbian.install',
        'rpst' => 'application/vnd.nokia.radio-preset',
        'rpss' => 'application/vnd.nokia.radio-presets',
        'edm' => 'application/vnd.novadigm.edm',
        'edx' => 'application/vnd.novadigm.edx',
        'ext' => 'application/vnd.novadigm.ext',
        'odc' => 'application/vnd.oasis.opendocument.chart',
        'otc' => 'application/vnd.oasis.opendocument.chart-template',
        'odb' => 'application/vnd.oasis.opendocument.database',
        'odf' => 'application/vnd.oasis.opendocument.formula',
        'odft' => 'application/vnd.oasis.opendocument.formula-template',
        'odg' => 'application/vnd.oasis.opendocument.graphics',
        'otg' => 'application/vnd.oasis.opendocument.graphics-template',
        'odi' => 'application/vnd.oasis.opendocument.image',
        'oti' => 'application/vnd.oasis.opendocument.image-template',
        'odp' => 'application/vnd.oasis.opendocument.presentation',
        'otp' => 'application/vnd.oasis.opendocument.presentation-template',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        'ots' => 'application/vnd.oasis.opendocument.spreadsheet-template',
        'odt' => 'application/vnd.oasis.opendocument.text',
        'odm' => 'application/vnd.oasis.opendocument.text-master',
        'ott' => 'application/vnd.oasis.opendocument.text-template',
        'oth' => 'application/vnd.oasis.opendocument.text-web',
        'xo' => 'application/vnd.olpc-sugar',
        'dd2' => 'application/vnd.oma.dd2+xml',
        'oxt' => 'application/vnd.openofficeorg.extension',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
        'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
        'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
        'mgp' => 'application/vnd.osgeo.mapguide.package',
        'dp' => 'application/vnd.osgi.dp',
        'pdb' => 'application/vnd.palm',
        'paw' => 'application/vnd.pawaafile',
        'str' => 'application/vnd.pg.format',
        'ei6' => 'application/vnd.pg.osasli',
        'efif' => 'application/vnd.picsel',
        'wg' => 'application/vnd.pmi.widget',
        'plf' => 'application/vnd.pocketlearn',
        'pbd' => 'application/vnd.powerbuilder6',
        'box' => 'application/vnd.previewsystems.box',
        'mgz' => 'application/vnd.proteus.magazine',
        'qps' => 'application/vnd.publishare-delta-tree',
        'ptid' => 'application/vnd.pvi.ptid1',
        'qxd' => 'application/vnd.quark.quarkxpress',
        'bed' => 'application/vnd.realvnc.bed',
        'mxl' => 'application/vnd.recordare.musicxml',
        'musicxml' => 'application/vnd.recordare.musicxml+xml',
        'cryptonote' => 'application/vnd.rig.cryptonote',
        'cod' => 'application/vnd.rim.cod',
        'rm' => 'application/vnd.rn-realmedia',
        'link66' => 'application/vnd.route66.link66+xml',
        'st' => 'application/vnd.sailingtracker.track',
        'see' => 'application/vnd.seemail',
        'sema' => 'application/vnd.sema',
        'semd' => 'application/vnd.semd',
        'semf' => 'application/vnd.semf',
        'ifm' => 'application/vnd.shana.informed.formdata',
        'itp' => 'application/vnd.shana.informed.formtemplate',
        'iif' => 'application/vnd.shana.informed.interchange',
        'ipk' => 'application/vnd.shana.informed.package',
        'twd' => 'application/vnd.simtech-mindmapper',
        'mmf' => 'application/vnd.smaf',
        'teacher' => 'application/vnd.smart.teacher',
        'sdkm' => 'application/vnd.solent.sdkm+xml',
        'dxp' => 'application/vnd.spotfire.dxp',
        'sfs' => 'application/vnd.spotfire.sfs',
        'sdc' => 'application/vnd.stardivision.calc',
        'sda' => 'application/vnd.stardivision.draw',
        'sdd' => 'application/vnd.stardivision.impress',
        'smf' => 'application/vnd.stardivision.math',
        'sdw' => 'application/vnd.stardivision.writer',
        'sgl' => 'application/vnd.stardivision.writer-global',
        'sm' => 'application/vnd.stepmania.stepchart',
        'sxc' => 'application/vnd.sun.xml.calc',
        'stc' => 'application/vnd.sun.xml.calc.template',
        'sxd' => 'application/vnd.sun.xml.draw',
        'std' => 'application/vnd.sun.xml.draw.template',
        'sxi' => 'application/vnd.sun.xml.impress',
        'sti' => 'application/vnd.sun.xml.impress.template',
        'sxm' => 'application/vnd.sun.xml.math',
        'sxw' => 'application/vnd.sun.xml.writer',
        'sxg' => 'application/vnd.sun.xml.writer.global',
        'stw' => 'application/vnd.sun.xml.writer.template',
        'sus' => 'application/vnd.sus-calendar',
        'svd' => 'application/vnd.svd',
        'sis' => 'application/vnd.symbian.install',
        'bdm' => 'application/vnd.syncml.dm+wbxml',
        'xdm' => 'application/vnd.syncml.dm+xml',
        'xsm' => 'application/vnd.syncml+xml',
        'tao' => 'application/vnd.tao.intent-module-archive',
        'tmo' => 'application/vnd.tmobile-livetv',
        'tpt' => 'application/vnd.trid.tpt',
        'mxs' => 'application/vnd.triscape.mxs',
        'tra' => 'application/vnd.trueapp',
        'ufd' => 'application/vnd.ufdl',
        'utz' => 'application/vnd.uiq.theme',
        'umj' => 'application/vnd.umajin',
        'unityweb' => 'application/vnd.unity',
        'uoml' => 'application/vnd.uoml+xml',
        'vcx' => 'application/vnd.vcx',
        'vsd' => 'application/vnd.visio',
        'vsdx' => 'application/vnd.visio2013',
        'vis' => 'application/vnd.visionary',
        'wbxml' => 'application/vnd.wap.wbxml',
        'wmlc' => 'application/vnd.wap.wmlc',
        'wmlsc' => 'application/vnd.wap.wmlscriptc',
        'wtls-ca-certificate' => 'application/vnd.wap.wtls-ca-certificate',
        'wtb' => 'application/vnd.webturbo',
        'nbp' => 'application/vnd.wolfram.player',
        'wpd' => 'application/vnd.wordperfect',
        'wqd' => 'application/vnd.wqd',
        'stf' => 'application/vnd.wt.stf',
        'xar' => 'application/vnd.xara',
        'xfdl' => 'application/vnd.xfdl',
        'hvd' => 'application/vnd.yamaha.hv-dic',
        'hvs' => 'application/vnd.yamaha.hv-script',
        'hvp' => 'application/vnd.yamaha.hv-voice',
        'osf' => 'application/vnd.yamaha.openscoreformat',
        'osfpvg' => 'application/vnd.yamaha.openscoreformat.osfpvg+xml',
        'saf' => 'application/vnd.yamaha.smaf-audio',
        'spf' => 'application/vnd.yamaha.smaf-phrase',
        'cmp' => 'application/vnd.yellowriver-custom-menu',
        'zir' => 'application/vnd.zul',
        'zaz' => 'application/vnd.zzazz.deck+xml',
        'vxml' => 'application/voicexml+xml',
        'wasm' => 'application/wasm',
        'wgt' => 'application/widget',
        'hlp' => 'application/winhlp',
        'wsdl' => 'application/wsdl+xml',
        'wspolicy' => 'application/wspolicy+xml',
        '7z' => 'application/x-7z-compressed',
        'abw' => 'application/x-abiword',
        'ace' => 'application/x-ace-compressed',
        'dmg' => 'application/x-apple-diskimage',
        'aab' => 'application/x-authorware-bin',
        'aam' => 'application/x-authorware-map',
        'aas' => 'application/x-authorware-seg',
        'bbaw' => 'application/x-bb-appworld',
        'bcpio' => 'application/x-bcpio',
        'torrent' => 'application/x-bittorrent',
        'bz' => 'application/x-bzip',
        'bz2' => 'application/x-bzip',
        'xdf' => 'application/xcap-diff+xml',
        'iso' => 'application/x-cd-image',
        'vcd' => 'application/x-cdlink',
        'chat' => 'application/x-chat',
        'pgn' => 'application/x-chess-pgn',
        'crx' => 'application/x-chrome-extension',
        'cco' => 'application/x-cocoa',
        'cpio' => 'application/x-cpio',
        'csh' => 'application/x-csh',
        'deb' => 'application/x-debian-package',
        'dcr' => 'application/x-director',
        'dir' => 'application/x-director',
        'dxr' => 'application/x-director',
        'wad' => 'application/x-doom',
        'ncx' => 'application/x-dtbncx+xml',
        'dtb' => 'application/x-dtbook+xml',
        'res' => 'application/x-dtbresource+xml',
        'dvi' => 'application/x-dvi',
        'xenc' => 'application/xenc+xml',
        'exe' => 'application/x-executable',
        'bdf' => 'application/x-font-bdf',
        'gsf' => 'application/x-font-ghostscript',
        'psf' => 'application/x-font-linux-psf',
        'pcf' => 'application/x-font-pcf',
        'snf' => 'application/x-font-snf',
        'pfa' => 'application/x-font-type1',
        'woff_o1' => 'application/x-font-woff',
        'spl' => 'application/x-futuresplash',
        'gnumeric' => 'application/x-gnumeric',
        'gtar' => 'application/x-gtar',
        'hdf' => 'application/x-hdf',
        'xht' => 'application/xhtml+xml',
        'xhtml' => 'application/xhtml+xml',
        'shtml' => 'application/x-httpd-shtml',
        'jardiff' => 'application/x-java-archive-diff',
        'jnlp' => 'application/x-java-jnlp-file',
        'latex' => 'application/x-latex',
        'run' => 'application/x-makeself',
        'xml' => 'application/xml',
        'xsd' => 'application/xml',
        'xsl' => 'application/xml',
        'dtd' => 'application/xml-dtd',
        'prc' => 'application/x-mobipocket-ebook',
        'application' => 'application/x-ms-application',
        'obd' => 'application/x-msbinder',
        'crd' => 'application/x-mscardfile',
        'clp' => 'application/x-msclip',
        'mvb' => 'application/x-msmediaview',
        'wmf' => 'application/x-msmetafile',
        'mny' => 'application/x-msmoney',
        'pub' => 'application/x-mspublisher',
        'scd' => 'application/x-msschedule',
        'trm' => 'application/x-msterminal',
        'wmd' => 'application/x-ms-wmd',
        'wmz' => 'application/x-ms-wmz',
        'xbap' => 'application/x-ms-xbap',
        'cdf' => 'application/x-netcdf',
        'nc' => 'application/x-netcdf',
        'xop' => 'application/xop+xml',
        'oex' => 'application/x-opera-extension',
        'pl' => 'application/x-perl',
        'pm' => 'application/x-perl',
        'p12' => 'application/x-pkcs12',
        'p7b' => 'application/x-pkcs7-certificates',
        'p7r' => 'application/x-pkcs7-certreqresp',
        'rar' => 'application/x-rar-compressed',
        'rpm' => 'application/x-redhat-package-manager',
        'sea' => 'application/x-sea',
        'sh' => 'application/x-sh',
        'shar' => 'application/x-shar',
        'swf' => 'application/x-shockwave-flash',
        'xap' => 'application/x-silverlight-app',
        'xslt' => 'application/xslt+xml',
        'xspf' => 'application/xspf+xml',
        'sit' => 'application/x-stuffit',
        'sitx' => 'application/x-stuffitx',
        'sv4cpio' => 'application/x-sv4cpio',
        'sv4crc' => 'application/x-sv4crc',
        'tar' => 'application/x-tar',
        'tcl' => 'application/x-tcl',
        'tk' => 'application/x-tcl',
        'tex' => 'application/x-tex',
        'texi' => 'application/x-texinfo',
        'texinfo' => 'application/x-texinfo',
        'tfm' => 'application/x-tex-tfm',
        'man' => 'application/x-troff-man',
        'me' => 'application/x-troff-me',
        'ms' => 'application/x-troff-ms',
        'ustar' => 'application/x-ustar',
        'mxml' => 'application/xv+xml',
        'src' => 'application/x-wais-source',
        'webapp' => 'application/x-web-app-manifest+json',
        'crt' => 'application/x-x509-ca-cert',
        'der' => 'application/x-x509-ca-cert',
        'pem' => 'application/x-x509-ca-cert',
        'fig' => 'application/x-xfig',
        'xpi' => 'application/x-xpinstall',
        'yang' => 'application/yang',
        'yin' => 'application/yin+xml',
        'zip' => 'application/zip',
        'adp' => 'audio/adpcm',
        'au' => 'audio/basic',
        'snd' => 'audio/basic',
        'flac' => 'audio/flac',
        'kar' => 'audio/midi',
        'mid' => 'audio/midi',
        'midi' => 'audio/midi',
        'f4a' => 'audio/mp1',
        'f4b' => 'audio/mp2',
        'm4a' => 'audio/mp3',
        'mp4a' => 'audio/mp4',
        'mp2' => 'audio/mpeg',
        'mp3' => 'audio/mpeg',
        'mpga' => 'audio/mpeg',
        'oga' => 'audio/ogg',
        'ogg' => 'audio/ogg',
        'opus' => 'audio/ogg',
        'uva' => 'audio/vnd.dece.audio',
        'eol' => 'audio/vnd.digital-winds',
        'dra' => 'audio/vnd.dra',
        'dts' => 'audio/vnd.dts',
        'dtshd' => 'audio/vnd.dts.hd',
        'lvp' => 'audio/vnd.lucent.voice',
        'pya' => 'audio/vnd.ms-playready.media.pya',
        'ecelp4800' => 'audio/vnd.nuera.ecelp4800',
        'ecelp7470' => 'audio/vnd.nuera.ecelp7470',
        'ecelp9600' => 'audio/vnd.nuera.ecelp9600',
        'rip' => 'audio/vnd.rip',
        'wav' => 'audio/wav',
        'weba' => 'audio/webm',
        'aac' => 'audio/x-aac',
        'aif' => 'audio/x-aiff',
        'aifc' => 'audio/x-aiff',
        'aiff' => 'audio/x-aiff',
        'm3u' => 'audio/x-mpegurl',
        'wax' => 'audio/x-ms-wax',
        'wma' => 'audio/x-ms-wma',
        'ram' => 'audio/x-pn-realaudio',
        'rmp' => 'audio/x-pn-realaudio-plugin',
        'ra' => 'audio/x-realaudio',
        'cdx' => 'chemical/x-cdx',
        'cif' => 'chemical/x-cif',
        'cmdf' => 'chemical/x-cmdf',
        'cml' => 'chemical/x-cml',
        'csml' => 'chemical/x-csml',
        'xyz' => 'chemical/x-xyz',
        'ttc' => 'font/collection',
        'otf' => 'font/otf',
        'ttf' => 'font/ttf',
        'ttf2' => 'font/ttf',
        'woff' => 'font/woff',
        'woff3' => 'font/woff',
        'woff2' => 'font/woff2',
        'avif' => 'image/avif',
        'avifs' => 'image/avif-sequence',
        'bmp' => 'image/bmp',
        'cgm' => 'image/cgm',
        'g3' => 'image/g3fax',
        'gif' => 'image/gif',
        'ief' => 'image/ief',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'jxl' => 'image/jxl',
        'ktx' => 'image/ktx',
        'pjpeg' => 'image/pjpeg',
        'png' => 'image/png',
        'btif' => 'image/prs.btif',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        'tif' => 'image/tiff',
        'tiff' => 'image/tiff',
        'psd' => 'image/vnd.adobe.photoshop',
        'uvi' => 'image/vnd.dece.graphic',
        'djvu' => 'image/vnd.djvu',
        'sub' => 'image/vnd.dvb.subtitle',
        'dwg' => 'image/vnd.dwg',
        'dxf' => 'image/vnd.dxf',
        'fbs' => 'image/vnd.fastbidsheet',
        'fpx' => 'image/vnd.fpx',
        'fst' => 'image/vnd.fst',
        'mmr' => 'image/vnd.fujixerox.edmics-mmr',
        'rlc' => 'image/vnd.fujixerox.edmics-rlc',
        'mdi' => 'image/vnd.ms-modi',
        'npx' => 'image/vnd.net-fpx',
        'wbmp' => 'image/vnd.wap.wbmp',
        'xif' => 'image/vnd.xiff',
        'webp' => 'image/webp',
        'ras' => 'image/x-cmu-raster',
        'cmx' => 'image/x-cmx',
        'fh' => 'image/x-freehand',
        'cur' => 'image/x-icon',
        'ico' => 'image/x-icon',
        'jng' => 'image/x-jng',
        'pcx' => 'image/x-pcx',
        'pic' => 'image/x-pict',
        'pnm' => 'image/x-portable-anymap',
        'pbm' => 'image/x-portable-bitmap',
        'pgm' => 'image/x-portable-graymap',
        'ppm' => 'image/x-portable-pixmap',
        'rgb' => 'image/x-rgb',
        'xbm' => 'image/x-xbitmap',
        'xpm' => 'image/x-xpixmap',
        'xwd' => 'image/x-xwindowdump',
        'eml' => 'message/rfc822',
        'iges' => 'model/iges',
        'igs' => 'model/iges',
        'mesh' => 'model/mesh',
        'msh' => 'model/mesh',
        'silo' => 'model/mesh',
        'dae' => 'model/vnd.collada+xml',
        'dwf' => 'model/vnd.dwf',
        'gdl' => 'model/vnd.gdl',
        'gtw' => 'model/vnd.gtw',
        'mts' => 'model/vnd.mts',
        'vtu' => 'model/vnd.vtu',
        'vrml' => 'model/vrml',
        'wrl' => 'model/vrml',
        'appcache' => 'text/cache-manifest',
        'ics' => 'text/calendar',
        'css' => 'text/css',
        'csv' => 'text/csv',
        'htm' => 'text/html',
        'html' => 'text/html',
        'js' => 'text/javascript',
        'js2' => 'text/javascript',
        'js3' => 'text/javascript',
        'mjs' => 'text/javascript',
        'markdown' => 'text/markdown',
        'md' => 'text/markdown',
        'mml' => 'text/mathml',
        'n3' => 'text/n3',
        'asc' => 'text/plain',
        'java' => 'text/plain',
        'jsp' => 'text/plain',
        'log' => 'text/plain',
        'txt' => 'text/plain',
        'par' => 'text/plain-bas',
        'dsc' => 'text/prs.lines.tag',
        'rtx' => 'text/richtext',
        'sgm' => 'text/sgml',
        'sgml' => 'text/sgml',
        'tsv' => 'text/tab-separated-values',
        'roff' => 'text/troff',
        't' => 'text/troff',
        'tr' => 'text/troff',
        'ttl' => 'text/turtle',
        'uri' => 'text/uri-list',
        'vcard' => 'text/vcard',
        'vcf' => 'text/vcard',
        'curl' => 'text/vnd.curl',
        'dcurl' => 'text/vnd.curl.dcurl',
        'mcurl' => 'text/vnd.curl.mcurl',
        'scurl' => 'text/vnd.curl.scurl',
        'fly' => 'text/vnd.fly',
        'flx' => 'text/vnd.fmi.flexstor',
        'gv' => 'text/vnd.graphviz',
        '3dml' => 'text/vnd.in3d.3dml',
        'spot' => 'text/vnd.in3d.spot',
        'xloc' => 'text/vnd.rim.location.xloc',
        'jad' => 'text/vnd.sun.j2me.app-descriptor',
        'wml' => 'text/vnd.wap.wml',
        'wmls' => 'text/vnd.wap.wmlscript',
        'vtt' => 'text/vtt',
        's' => 'text/x-asm',
        'c' => 'text/x-c',
        'htc' => 'text/x-component',
        'f' => 'text/x-fortran',
        'p' => 'text/x-pascal',
        'etx' => 'text/x-setext',
        'sql' => 'text/x-sql',
        'uu' => 'text/x-uuencode',
        'vcs' => 'text/x-vcalendar',
        'yaml' => 'text/yaml',
        '3gp' => 'video/3gpp',
        '3gpp' => 'video/3gpp',
        '3g2' => 'video/3gpp2',
        'asf' => 'video/asf',
        'asx' => 'video/asf',
        'h261' => 'video/h261',
        'h263' => 'video/h263',
        'h264' => 'video/h264',
        'jpgv' => 'video/jpeg',
        'jpm' => 'video/jpm',
        'mj2' => 'video/mj2',
        'f4v' => 'video/mp1',
        'f4p' => 'video/mp2',
        'ts' => 'video/mp2t',
        'm4v' => 'video/mp3',
        'mp4' => 'video/mp4',
        'mpe' => 'video/mpeg',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'ogv' => 'video/ogg',
        'mov' => 'video/quicktime',
        'qt' => 'video/quicktime',
        'qtvr' => 'video/quicktime',
        'uvh' => 'video/vnd.dece.hd',
        'uvm' => 'video/vnd.dece.mobile',
        'uvp' => 'video/vnd.dece.pd',
        'uvs' => 'video/vnd.dece.sd',
        'uvv' => 'video/vnd.dece.video',
        'fvt' => 'video/vnd.fvt',
        'mxu' => 'video/vnd.mpegurl',
        'pyv' => 'video/vnd.ms-playready.media.pyv',
        'uvu' => 'video/vnd.uvvu.mp4',
        'viv' => 'video/vnd.vivo',
        'webm' => 'video/webm',
        'fli' => 'video/x-fli',
        'flv' => 'video/x-flv',
        'mng' => 'video/x-mng',
        'avi' => 'video/x-msvideo',
        'wm' => 'video/x-ms-wm',
        'wmv' => 'video/x-ms-wmv',
        'wmx' => 'video/x-ms-wmx',
        'wvx' => 'video/x-ms-wvx',
        'movie' => 'video/x-sgi-movie',
        'ice' => 'x-conference/x-cooltalk',
    ];
    
    /**
     * Wrapper for date(), that handles strings and allows validation of the result
     * @param string|int|float|null $time        Time value
     * @param string                $format      Expected format
     * @param string                $valid_regex Regex to use for validation
     *
     * @return string
     */
    public static function valueToTime(string|int|float|null $time, string $format, string $valid_regex = ''): string
    {
        #If we want to use a constant, but it was sent as a string
        if (str_starts_with(mb_strtoupper($format, 'UTF-8'), 'DATE_')) {
            $format = \constant($format);
        }
        if (empty($time)) {
            $time = \date($format);
        } elseif (\is_numeric($time)) {
            #Ensure we use int
            $time = \date($format, (int)$time);
        } elseif (is_string($time)) {
            #Attempt to convert string to time
            $time = \date($format, \strtotime($time));
        } else {
            throw new \UnexpectedValueException('Time provided to `valueToTime` is neither numeric or string');
        }
        if ($format === 'c' || $format === \DATE_ATOM) {
            $valid_regex = '/^(?:[1-9]\d{3}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1\d|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[1-9]\d(?:0[48]|[2468][048]|[13579][26])|(?:[2468][048]|[13579][26])00)-02-29)T(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d(?:Z|[+-][01]\d:[0-5]\d)$/i';
        }
        if (!empty($valid_regex) && \preg_match($valid_regex, $time) !== 1) {
            throw new \UnexpectedValueException('Date provided to `valueToTime` failed to be validated against the provided regex');
        }
        return $time;
    }
    
    /**
     * Function uses ob functions to attempt compressing output sent to browser and also provide browser with length of the output and some caching-related headers
     *
     * @param string $string         String to echo
     * @param string $cache_strategy Cache strategy (same as for `cacheControl` function)
     * @param bool   $exit           Whether to stop execution after echoing or not
     *
     * @return void
     */
    public static function zEcho(string $string, #[ExpectedValues(['', 'aggressive', 'private', 'none', 'live', 'month', 'week', 'day', 'hour'])] string $cache_strategy = '', bool $exit = true): void
    {
        #Close session
        if (\session_status() === \PHP_SESSION_ACTIVE) {
            \session_write_close();
        }
        $postfix = '';
        if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            #Attempt brotli compression, if available and client supports it
            if (extension_loaded('brotli') && str_contains($_SERVER['HTTP_ACCEPT_ENCODING'], 'br')) {
                #Compress string
                $string = \brotli_compress($string, 11, \BROTLI_TEXT);
                #Send header with format
                if (!\headers_sent()) {
                    \header('Content-Encoding: br');
                }
                $postfix = '-br';
                #Check that zlib is loaded and client supports GZip. We are ignoring Deflate because of known inconsistencies with how it is handled by browsers depending on whether it is wrapped in Zlib or not.
            } elseif (extension_loaded('zlib') && str_contains($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
                #It is recommended to use ob_gzhandler or zlib.output_compression, but I am getting inconsistent results with headers when using them, thus this "direct" approach.
                #GZipping the string
                $string = \gzcompress($string, 9, \FORCE_GZIP);
                #Send header with format
                if (!\headers_sent()) {
                    \header('Content-Encoding: gzip');
                }
                $postfix = '-gzip';
            }
        }
        Headers::cacheControl($string, $cache_strategy, true, $postfix);
        #Send header with length
        if (!\headers_sent()) {
            \header('Content-Length: '.\strlen($string));
        }
        #Some HTTP methods do not support body, thus we need to ensure it's not sent.
        $method = $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] ?? $_SERVER['REQUEST_METHOD'] ?? null;
        if (in_array($method, ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'])) {
            #Send the output
            echo $string;
        }
        if ($exit) {
            exit(0);
        }
    }
    
    /**
     * Function to check if string is a valid language code
     * @param string $string
     *
     * @return bool
     */
    public static function langCodeCheck(string $string): bool
    {
        return in_array(mb_strtolower($string, 'UTF-8'),
            ['af', 'sq', 'eu', 'be', 'bg', 'ca', 'zh-cn', 'zh-tw', 'hr', 'cs', 'da', 'nl', 'nl-be', 'nl-nl', 'en', 'en-au', 'en-bz', 'en-ca', 'en-ie', 'en-jm', 'en-nz', 'en-ph', 'en-za', 'en-tt', 'en-gb', 'en-us', 'en-zw', 'et', 'fo', 'fi', 'fr', 'fr-be', 'fr-ca', 'fr-fr', 'fr-lu', 'fr-mc', 'fr-ch', 'gl', 'gd', 'de', 'de-at', 'de-de', 'de-li', 'de-lu', 'de-ch', 'el', 'haw', 'hu', 'is', 'in', 'ga', 'it', 'it-it', 'it-ch', 'ja', 'ko', 'mk', 'no', 'pl', 'pt', 'pt-br', 'pt-pt', 'ro', 'ro-mo', 'ro-ro', 'ru', 'ru-mo', 'ru-ru', 'sr', 'sk', 'sl', 'es', 'es-ar', 'es-bo', 'es-cl', 'es-co', 'es-cr', 'es-do', 'es-ec', 'es-sv', 'es-gt', 'es-hn', 'es-mx', 'es-ni', 'es-pa', 'es-py', 'es-pe', 'es-pr', 'es-es', 'es-uy', 'es-ve', 'sv', 'sv-fi', 'sv-se', 'tr', 'uk']
        );
    }
    
    /**
     * Function does the same as `rawurlencode`, but only for selected characters, that are restricted in HTML/XML. Useful for URIs that can have these characters and need to be used in HTML/XML and thus can't use `htmlentities`, but otherwise break HTML/XML
     * @param string $string String to encode
     * @param bool   $full   Means that all characters will be converted (useful when text inside a tag). If `false` only `<` and `&` are converted (useful when inside attribute). If `false` is used - be careful with quotes inside the string you provide, because they can invalidate your HTML/XML
     *
     * @return string
     */
    public static function htmlToRFC3986(string $string, bool $full = true): string
    {
        if ($full) {
            return \str_replace(['\'', '"', '&', '<', '>'], ['%27', '%22', '%26', '%3C', '%3E'], $string);
        }
        return \str_replace(['&', '<'], ['%26', '%3C'], $string);
    }
    
    /**
     * Function to merge CSS/JS files to reduce the number of connections to your server, yet allow you to keep the files separate for easier development. It also allows you to minify the result for extra size saving, but be careful with that. #Minification is based on https://gist.github.com/Rodrigo54/93169db48194d470188f
     *
     * @param string|array $files          File(s) to process
     * @param string       $type           File(s) type (`css`, `js` or `html`)
     * @param bool         $minify         Whether to minify the output
     * @param string       $to_file        Optional path to a file, if you want to save the result
     * @param string       $cache_strategy Cache strategy (same as `cacheStrategy` function)
     *
     * @return void
     */
    public static function reductor(string|array $files, #[ExpectedValues('css', 'js', 'html')] string $type, bool $minify = false, string $to_file = '', string $cache_strategy = ''): void
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
        #Prepare the array of dates
        $dates = [];
        #Iterate array
        foreach ($files as $file) {
            #Check if string is a file
            if (\is_file($file)) {
                #Check extension
                if (\strcasecmp(\pathinfo($file, \PATHINFO_EXTENSION), $type) === 0) {
                    #Add date to list
                    $dates[] = \filemtime($file);
                    #Add contents
                    $content .= \file_get_contents($file);
                }
            } elseif (\is_dir($file)) {
                $file_list = (new \RecursiveIteratorIterator((new \RecursiveDirectoryIterator($file, \FilesystemIterator::FOLLOW_SYMLINKS | \FilesystemIterator::SKIP_DOTS)), \RecursiveIteratorIterator::SELF_FIRST));
                foreach ($file_list as $sub_file) {
                    if (\strcasecmp($sub_file->getExtension(), $type) === 0) {
                        #Add date to list
                        $dates[] = $sub_file->getMTime();
                        #Add contents
                        $content .= \file_get_contents($sub_file->getRealPath());
                    }
                }
            }
        }
        #Get date if we are directly outputting the data
        if (empty($to_file)) {
            #Send Last-Modified header and exit if we hit browser cache
            Headers::lastModified(\max($dates), true);
        }
        #Minify
        if ($minify) {
            switch (mb_strtolower($type, 'UTF-8')) {
                case 'js':
                    $content = \preg_replace(
                        [
                            // Remove comment(s)
                            '#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*/\*(?!!|@cc_on)(?>[\s\S]*?\*/)\s*|\s*(?<![:=])//.*(?=[\n\r]|$)|^\s*|\s*$#',
                            // Remove white-space(s) outside the string and regex
                            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|/\*(?>.*?\*/)|/(?!/)[^\n\r]*?/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*()\-=+\[\]{}|;:,.<>?/])\s*#s',
                            // Remove the last semicolon
                            '#;+}#',
                            // Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`
                            '#([{,])(\')(\d+|[a-z_][a-z0-9_]*)\2(?=:)#i',
                            // --ibid. From `foo['bar']` to `foo.bar`
                            '#([a-z0-9_)\]])\[([\'"])([a-z_][a-z0-9_]*)\2]#i'
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
                    $content = \preg_replace(
                        [
                            // Remove comment(s)
                            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|/\*(?!!)(?>.*?\*/)|^\s*|\s*$#s',
                            // Remove unused white-space(s)
                            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|/\*(?>.*?\*/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~]|\s(?![0-9.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
                            // Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
                            '#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#i',
                            // Replace `:0 0 0 0` with `:0`
                            '#:(0\s+0|0\s+0\s+0\s+0)(?=[;}]|!important)#i',
                            // Replace `background-position:0` with `background-position:0 0`
                            '#(background-position):0(?=[;}])#i',
                            // Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space
                            '#(?<=[\s:,\-])0+\.(\d+)#',
                            // Minify string value
                            '#(/\*(?>.*?\*/))|(?<!content:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s{}\];,])#si',
                            '#(/\*(?>.*?\*/))|(\burl\()([\'"])(\S+?)\3(\))#si',
                            // Minify HEX color code
                            '#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
                            // Replace `(border|outline):none` with `(border|outline):0`
                            '#(?<=[{;])(border|outline):none(?=[;}!])#',
                            // Remove empty selector(s)
                            '#(/\*(?>.*?\*/))|(^|[{}])[^\s{}]+{}#s'
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
                    $content = \preg_replace_callback('#<([^/\s<>!]+)(?:\s+([^<>]*?)\s*|\s*)(/?)>#',
                        static function ($matches) {
                            return '<'.$matches[1].\preg_replace('#([^\s=]+)(=([\'"]?)(.*?)\3)?(\s+|$)#s', ' $1$2', $matches[2]).$matches[3].'>';
                        }, \str_replace("\r", '', $content));
                    $content = \preg_replace(
                        [
                            // t = text
                            // o = tag open
                            // c = tag close
                            // Keep important white-space(s) after self-closing HTML tag(s)
                            '#<(img|input)(>| .*?>)#s',
                            // Remove a line break and two or more white-space(s) between tag(s)
                            '#(<!--.*?-->)|(>)(?:\n*|\s{2,})(<)|^\s*|\s*$#s',
                            '#(<!--.*?-->)|(?<!>)\s+(</.*?>)|(<[^/]*?>)\s+(?!<)#s', // t+c || o+t
                            '#(<!--.*?-->)|(<[^/]*?>)\s+(<[^/]*?>)|(</.*?>)\s+(</.*?>)#s', // o+o || c+c
                            '#(<!--.*?-->)|(</.*?>)\s+(\s)(?!<)|(?<!>)\s+(\s)(<[^/]*?/?>)|(<[^/]*?/?>)\s+(\s)(?!<)#s', // c+t || t+o || o+t -- separated by long white-space(s)
                            '#(<!--.*?-->)|(<[^/]*?>)\s+(</.*?>)#s', // empty tag
                            '#<(img|input)(>| .*?>)</\1>#s', // reset previous fix
                            '#(&nbsp;)&nbsp;(?![<\s])#', // clean up ...
                            '#(?<=>)(&nbsp;)(?=<)#', // --ibid
                            // Remove HTML comment(s) except IE comment(s)
                            '#\s*<!--(?!\[if\s).*?-->\s*|(?<!>)\n+(?=<[^!])#s'
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
        if (empty($to_file)) {
            #Send the appropriate header
            switch (mb_strtolower($type, 'UTF-8')) {
                case 'js':
                    if (!\headers_list()) {
                        \header('Content-Type: application/javascript; charset=utf-8');
                    }
                    break;
                case 'css':
                    if (!\headers_list()) {
                        \header('Content-Type: text/css; charset=utf-8');
                    }
                    break;
                default:
                    if (!\headers_list()) {
                        \header('Content-Type: text/html; charset=utf-8');
                    }
                    break;
            }
            #Send data to browser
            self::zEcho($content, $cache_strategy);
        } else {
            \file_put_contents($to_file, $content);
        }
    }
    
    /**
     * Function to force close HTTP connection. Possible notices from `ob_end_clean` and `flush` are suppressed, since I do not see a good alternative to this, when closing connection, which may be closed in a non-planned way.
     *
     * @return void
     * @noinspection PhpUsageOfSilenceOperatorInspection
     */
    #[NoReturn] public static function forceClose(): void
    {
        #Close session
        if (\session_status() === \PHP_SESSION_ACTIVE) {
            \session_write_close();
        }
        #Send header to notify, that connection was closed
        if (!\headers_sent()) {
            \header('Connection: close');
        }
        #Clean output buffer and close it
        @\ob_end_clean();
        #Clean system buffer
        @\flush();
        exit(0);
    }
}
