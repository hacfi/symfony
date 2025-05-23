<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Intl\Tests;

use Symfony\Component\Intl\Exception\MissingResourceException;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Intl\Util\IntlTestHelper;

/**
 * @group intl-data
 */
class LanguagesTest extends ResourceBundleTestCase
{
    // The below arrays document the state of the ICU data bundled with this package.

    private const LANGUAGES = [
        'aa',
        'ab',
        'ace',
        'ach',
        'ada',
        'ady',
        'ae',
        'aeb',
        'af',
        'afh',
        'agq',
        'ain',
        'ak',
        'akk',
        'akz',
        'ale',
        'aln',
        'alt',
        'am',
        'an',
        'ang',
        'ann',
        'anp',
        'apc',
        'ar',
        'arc',
        'arn',
        'aro',
        'arp',
        'arq',
        'ars',
        'arw',
        'ary',
        'arz',
        'as',
        'asa',
        'ase',
        'ast',
        'atj',
        'av',
        'avk',
        'awa',
        'ay',
        'az',
        'ba',
        'bal',
        'ban',
        'bar',
        'bas',
        'bax',
        'bbc',
        'bbj',
        'be',
        'bej',
        'bem',
        'bew',
        'bez',
        'bfd',
        'bfq',
        'bg',
        'bgc',
        'bgn',
        'bho',
        'bi',
        'bik',
        'bin',
        'bjn',
        'bkm',
        'bla',
        'blo',
        'blt',
        'bm',
        'bn',
        'bo',
        'bpy',
        'bqi',
        'br',
        'bra',
        'brh',
        'brx',
        'bs',
        'bss',
        'bua',
        'bug',
        'bum',
        'byn',
        'byv',
        'ca',
        'cad',
        'car',
        'cay',
        'cch',
        'ccp',
        'ce',
        'ceb',
        'cgg',
        'ch',
        'chb',
        'chg',
        'chk',
        'chm',
        'chn',
        'cho',
        'chp',
        'chr',
        'chy',
        'cic',
        'ckb',
        'clc',
        'co',
        'cop',
        'cps',
        'cr',
        'crg',
        'crh',
        'crj',
        'crk',
        'crl',
        'crm',
        'crr',
        'crs',
        'cs',
        'csb',
        'csw',
        'cu',
        'cv',
        'cy',
        'da',
        'dak',
        'dar',
        'dav',
        'de',
        'del',
        'den',
        'dgr',
        'din',
        'dje',
        'doi',
        'dsb',
        'dtp',
        'dua',
        'dum',
        'dv',
        'dyo',
        'dyu',
        'dz',
        'dzg',
        'ebu',
        'ee',
        'efi',
        'egl',
        'egy',
        'eka',
        'el',
        'elx',
        'en',
        'enm',
        'eo',
        'es',
        'esu',
        'et',
        'eu',
        'ewo',
        'ext',
        'fa',
        'fan',
        'fat',
        'ff',
        'fi',
        'fil',
        'fit',
        'fj',
        'fo',
        'fon',
        'fr',
        'frc',
        'frm',
        'fro',
        'frp',
        'frr',
        'frs',
        'fur',
        'fy',
        'ga',
        'gaa',
        'gag',
        'gan',
        'gay',
        'gba',
        'gbz',
        'gd',
        'gez',
        'gil',
        'gl',
        'glk',
        'gmh',
        'gn',
        'goh',
        'gon',
        'gor',
        'got',
        'grb',
        'grc',
        'gsw',
        'gu',
        'guc',
        'gur',
        'guz',
        'gv',
        'gwi',
        'ha',
        'hai',
        'hak',
        'haw',
        'hax',
        'he',
        'hi',
        'hif',
        'hil',
        'hit',
        'hmn',
        'hnj',
        'ho',
        'hr',
        'hsb',
        'hsn',
        'ht',
        'hu',
        'hup',
        'hur',
        'hy',
        'hz',
        'ia',
        'iba',
        'ibb',
        'id',
        'ie',
        'ig',
        'ii',
        'ik',
        'ikt',
        'ilo',
        'inh',
        'io',
        'is',
        'it',
        'iu',
        'izh',
        'ja',
        'jam',
        'jbo',
        'jgo',
        'jmc',
        'jpr',
        'jrb',
        'jut',
        'jv',
        'ka',
        'kaa',
        'kab',
        'kac',
        'kaj',
        'kam',
        'kaw',
        'kbd',
        'kbl',
        'kcg',
        'kde',
        'kea',
        'ken',
        'kfo',
        'kg',
        'kgp',
        'kha',
        'kho',
        'khq',
        'khw',
        'ki',
        'kiu',
        'kj',
        'kk',
        'kkj',
        'kl',
        'kln',
        'km',
        'kmb',
        'kn',
        'ko',
        'koi',
        'kok',
        'kos',
        'kpe',
        'kr',
        'krc',
        'kri',
        'krj',
        'krl',
        'kru',
        'ks',
        'ksb',
        'ksf',
        'ksh',
        'ku',
        'kum',
        'kut',
        'kv',
        'kw',
        'kwk',
        'kxv',
        'ky',
        'la',
        'lad',
        'lag',
        'lah',
        'lam',
        'lb',
        'lez',
        'lfn',
        'lg',
        'li',
        'lij',
        'lil',
        'liv',
        'lkt',
        'lld',
        'lmo',
        'ln',
        'lo',
        'lol',
        'lou',
        'loz',
        'lrc',
        'lsm',
        'lt',
        'ltg',
        'lu',
        'lua',
        'lui',
        'lun',
        'luo',
        'lus',
        'luy',
        'lv',
        'lzh',
        'lzz',
        'mad',
        'maf',
        'mag',
        'mai',
        'mak',
        'man',
        'mas',
        'mde',
        'mdf',
        'mdr',
        'men',
        'mer',
        'mfe',
        'mg',
        'mga',
        'mgh',
        'mgo',
        'mh',
        'mhn',
        'mi',
        'mic',
        'min',
        'mk',
        'ml',
        'mn',
        'mnc',
        'mni',
        'moe',
        'moh',
        'mos',
        'mr',
        'mrj',
        'ms',
        'mt',
        'mua',
        'mus',
        'mwl',
        'mwr',
        'mwv',
        'my',
        'mye',
        'myv',
        'mzn',
        'na',
        'nan',
        'nap',
        'naq',
        'nb',
        'nd',
        'nds',
        'ne',
        'new',
        'ng',
        'nia',
        'niu',
        'njo',
        'nl',
        'nmg',
        'nn',
        'nnh',
        'no',
        'nog',
        'non',
        'nov',
        'nqo',
        'nr',
        'nso',
        'nus',
        'nv',
        'nwc',
        'ny',
        'nym',
        'nyn',
        'nyo',
        'nzi',
        'oc',
        'oj',
        'ojb',
        'ojc',
        'ojs',
        'ojw',
        'oka',
        'om',
        'or',
        'os',
        'osa',
        'ota',
        'pa',
        'pag',
        'pal',
        'pam',
        'pap',
        'pau',
        'pcd',
        'pcm',
        'pdc',
        'pdt',
        'peo',
        'pfl',
        'phn',
        'pi',
        'pis',
        'pl',
        'pms',
        'pnt',
        'pon',
        'pqm',
        'prg',
        'pro',
        'ps',
        'pt',
        'qu',
        'quc',
        'qug',
        'raj',
        'rap',
        'rar',
        'rgn',
        'rhg',
        'rif',
        'rm',
        'rn',
        'ro',
        'rof',
        'rom',
        'rtm',
        'ru',
        'rue',
        'rug',
        'rup',
        'rw',
        'rwk',
        'sa',
        'sad',
        'sah',
        'sam',
        'saq',
        'sas',
        'sat',
        'saz',
        'sba',
        'sbp',
        'sc',
        'scn',
        'sco',
        'sd',
        'sdc',
        'sdh',
        'se',
        'see',
        'seh',
        'sei',
        'sel',
        'ses',
        'sg',
        'sga',
        'sgs',
        'sh',
        'shi',
        'shn',
        'shu',
        'si',
        'sid',
        'sk',
        'skr',
        'sl',
        'slh',
        'sli',
        'sly',
        'sm',
        'sma',
        'smj',
        'smn',
        'sms',
        'sn',
        'snk',
        'so',
        'sog',
        'sq',
        'sr',
        'srn',
        'srr',
        'ss',
        'ssy',
        'st',
        'stq',
        'str',
        'su',
        'suk',
        'sus',
        'sux',
        'sv',
        'sw',
        'swb',
        'syc',
        'syr',
        'szl',
        'ta',
        'tce',
        'tcy',
        'te',
        'tem',
        'teo',
        'ter',
        'tet',
        'tg',
        'tgx',
        'th',
        'tht',
        'ti',
        'tig',
        'tiv',
        'tk',
        'tkl',
        'tkr',
        'tl',
        'tlh',
        'tli',
        'tly',
        'tmh',
        'tn',
        'to',
        'tog',
        'tok',
        'tpi',
        'tr',
        'tru',
        'trv',
        'trw',
        'ts',
        'tsd',
        'tsi',
        'tt',
        'ttm',
        'ttt',
        'tum',
        'tvl',
        'tw',
        'twq',
        'ty',
        'tyv',
        'tzm',
        'udm',
        'ug',
        'uga',
        'uk',
        'umb',
        'ur',
        'uz',
        'vai',
        've',
        'vec',
        'vep',
        'vi',
        'vls',
        'vmf',
        'vmw',
        'vo',
        'vot',
        'vro',
        'vun',
        'wa',
        'wae',
        'wal',
        'war',
        'was',
        'wbp',
        'wo',
        'wuu',
        'xal',
        'xh',
        'xmf',
        'xnr',
        'xog',
        'yao',
        'yap',
        'yav',
        'ybb',
        'yi',
        'yo',
        'yrl',
        'yue',
        'za',
        'zap',
        'zbl',
        'zea',
        'zen',
        'zgh',
        'zh',
        'zu',
        'zun',
        'zza',
    ];

    private const ALPHA3_CODES = [
        'aar',
        'abk',
        'ace',
        'ach',
        'ada',
        'ady',
        'aeb',
        'afh',
        'afr',
        'agq',
        'ain',
        'aka',
        'akk',
        'akz',
        'ale',
        'aln',
        'alt',
        'amh',
        'ang',
        'ann',
        'anp',
        'apc',
        'ara',
        'arc',
        'arg',
        'arn',
        'aro',
        'arp',
        'arq',
        'ars',
        'arw',
        'ary',
        'arz',
        'asa',
        'ase',
        'asm',
        'ast',
        'atj',
        'ava',
        'ave',
        'avk',
        'awa',
        'aym',
        'aze',
        'bak',
        'bal',
        'bam',
        'ban',
        'bar',
        'bas',
        'bax',
        'bbc',
        'bbj',
        'bej',
        'bel',
        'bem',
        'ben',
        'bew',
        'bez',
        'bfd',
        'bfq',
        'bgc',
        'bgn',
        'bho',
        'bih',
        'bik',
        'bin',
        'bis',
        'bjn',
        'bkm',
        'bla',
        'blo',
        'blt',
        'bod',
        'bos',
        'bpy',
        'bqi',
        'bra',
        'bre',
        'brh',
        'brx',
        'bss',
        'bua',
        'bug',
        'bul',
        'bum',
        'byn',
        'byv',
        'cad',
        'car',
        'cat',
        'cay',
        'cch',
        'ccp',
        'ceb',
        'ces',
        'cgg',
        'cha',
        'chb',
        'che',
        'chg',
        'chk',
        'chm',
        'chn',
        'cho',
        'chp',
        'chr',
        'chu',
        'chv',
        'chy',
        'cic',
        'ckb',
        'clc',
        'cop',
        'cor',
        'cos',
        'cps',
        'cre',
        'crg',
        'crh',
        'crj',
        'crk',
        'crl',
        'crm',
        'crr',
        'crs',
        'csb',
        'csw',
        'cym',
        'dak',
        'dan',
        'dar',
        'dav',
        'del',
        'den',
        'deu',
        'dgr',
        'din',
        'div',
        'dje',
        'doi',
        'dsb',
        'dtp',
        'dua',
        'dum',
        'dyo',
        'dyu',
        'dzg',
        'dzo',
        'ebu',
        'efi',
        'egl',
        'egy',
        'eka',
        'ell',
        'elx',
        'eng',
        'enm',
        'epo',
        'est',
        'esu',
        'eus',
        'ewe',
        'ewo',
        'ext',
        'fan',
        'fao',
        'fas',
        'fat',
        'fij',
        'fil',
        'fin',
        'fit',
        'fon',
        'fra',
        'frc',
        'frm',
        'fro',
        'frp',
        'frr',
        'frs',
        'fry',
        'ful',
        'fur',
        'gaa',
        'gag',
        'gan',
        'gay',
        'gba',
        'gbz',
        'gez',
        'gil',
        'gla',
        'gle',
        'glg',
        'glk',
        'glv',
        'gmh',
        'goh',
        'gon',
        'gor',
        'got',
        'grb',
        'grc',
        'grn',
        'gsw',
        'guc',
        'guj',
        'gur',
        'guz',
        'gwi',
        'hai',
        'hak',
        'hat',
        'hau',
        'haw',
        'hax',
        'hbs',
        'heb',
        'her',
        'hif',
        'hil',
        'hin',
        'hit',
        'hmn',
        'hmo',
        'hnj',
        'hrv',
        'hsb',
        'hsn',
        'hun',
        'hup',
        'hur',
        'hye',
        'iba',
        'ibb',
        'ibo',
        'ido',
        'iii',
        'ikt',
        'iku',
        'ile',
        'ilo',
        'ina',
        'ind',
        'inh',
        'ipk',
        'isl',
        'ita',
        'izh',
        'jam',
        'jav',
        'jbo',
        'jgo',
        'jmc',
        'jpn',
        'jpr',
        'jrb',
        'jut',
        'kaa',
        'kab',
        'kac',
        'kaj',
        'kal',
        'kam',
        'kan',
        'kas',
        'kat',
        'kau',
        'kaw',
        'kaz',
        'kbd',
        'kbl',
        'kcg',
        'kde',
        'kea',
        'ken',
        'kfo',
        'kgp',
        'kha',
        'khm',
        'kho',
        'khq',
        'khw',
        'kik',
        'kin',
        'kir',
        'kiu',
        'kkj',
        'kln',
        'kmb',
        'koi',
        'kok',
        'kom',
        'kon',
        'kor',
        'kos',
        'kpe',
        'krc',
        'kri',
        'krj',
        'krl',
        'kru',
        'ksb',
        'ksf',
        'ksh',
        'kua',
        'kum',
        'kur',
        'kut',
        'kwk',
        'kxv',
        'lad',
        'lag',
        'lah',
        'lam',
        'lao',
        'lat',
        'lav',
        'lez',
        'lfn',
        'lij',
        'lil',
        'lim',
        'lin',
        'lit',
        'liv',
        'lkt',
        'lld',
        'lmo',
        'lol',
        'lou',
        'loz',
        'lrc',
        'lsm',
        'ltg',
        'ltz',
        'lua',
        'lub',
        'lug',
        'lui',
        'lun',
        'luo',
        'lus',
        'luy',
        'lzh',
        'lzz',
        'mad',
        'maf',
        'mag',
        'mah',
        'mai',
        'mak',
        'mal',
        'man',
        'mar',
        'mas',
        'mde',
        'mdf',
        'mdr',
        'men',
        'mer',
        'mfe',
        'mga',
        'mgh',
        'mgo',
        'mhn',
        'mic',
        'min',
        'mkd',
        'mlg',
        'mlt',
        'mnc',
        'mni',
        'moe',
        'moh',
        'mol',
        'mon',
        'mos',
        'mri',
        'mrj',
        'msa',
        'mua',
        'mus',
        'mwl',
        'mwr',
        'mwv',
        'mya',
        'mye',
        'myv',
        'mzn',
        'nan',
        'nap',
        'naq',
        'nau',
        'nav',
        'nbl',
        'nde',
        'ndo',
        'nds',
        'nep',
        'new',
        'nia',
        'niu',
        'njo',
        'nld',
        'nmg',
        'nnh',
        'nno',
        'nob',
        'nog',
        'non',
        'nor',
        'nov',
        'nqo',
        'nso',
        'nus',
        'nwc',
        'nya',
        'nym',
        'nyn',
        'nyo',
        'nzi',
        'oci',
        'ojb',
        'ojc',
        'oji',
        'ojs',
        'ojw',
        'oka',
        'ori',
        'orm',
        'osa',
        'oss',
        'ota',
        'pag',
        'pal',
        'pam',
        'pan',
        'pap',
        'pau',
        'pcd',
        'pcm',
        'pdc',
        'pdt',
        'peo',
        'pfl',
        'phn',
        'pis',
        'pli',
        'pms',
        'pnt',
        'pol',
        'pon',
        'por',
        'pqm',
        'prg',
        'pro',
        'prs',
        'pus',
        'quc',
        'que',
        'qug',
        'raj',
        'rap',
        'rar',
        'rgn',
        'rhg',
        'rif',
        'rof',
        'roh',
        'rom',
        'ron',
        'rtm',
        'rue',
        'rug',
        'run',
        'rup',
        'rus',
        'rwk',
        'sad',
        'sag',
        'sah',
        'sam',
        'san',
        'saq',
        'sas',
        'sat',
        'saz',
        'sba',
        'sbp',
        'scn',
        'sco',
        'sdc',
        'sdh',
        'see',
        'seh',
        'sei',
        'sel',
        'ses',
        'sga',
        'sgs',
        'shi',
        'shn',
        'shu',
        'sid',
        'sin',
        'skr',
        'slh',
        'sli',
        'slk',
        'slv',
        'sly',
        'sma',
        'sme',
        'smj',
        'smn',
        'smo',
        'sms',
        'sna',
        'snd',
        'snk',
        'sog',
        'som',
        'sot',
        'spa',
        'sqi',
        'srd',
        'srn',
        'srp',
        'srr',
        'ssw',
        'ssy',
        'stq',
        'str',
        'suk',
        'sun',
        'sus',
        'sux',
        'swa',
        'swb',
        'swc',
        'swe',
        'syc',
        'syr',
        'szl',
        'tah',
        'tam',
        'tat',
        'tce',
        'tcy',
        'tel',
        'tem',
        'teo',
        'ter',
        'tet',
        'tgk',
        'tgl',
        'tgx',
        'tha',
        'tht',
        'tig',
        'tir',
        'tiv',
        'tkl',
        'tkr',
        'tlh',
        'tli',
        'tly',
        'tmh',
        'tog',
        'tok',
        'ton',
        'tpi',
        'tru',
        'trv',
        'trw',
        'tsd',
        'tsi',
        'tsn',
        'tso',
        'ttm',
        'ttt',
        'tuk',
        'tum',
        'tur',
        'tvl',
        'twi',
        'twq',
        'tyv',
        'tzm',
        'udm',
        'uga',
        'uig',
        'ukr',
        'umb',
        'urd',
        'uzb',
        'vai',
        'vec',
        'ven',
        'vep',
        'vie',
        'vls',
        'vmf',
        'vmw',
        'vol',
        'vot',
        'vro',
        'vun',
        'wae',
        'wal',
        'war',
        'was',
        'wbp',
        'wln',
        'wol',
        'wuu',
        'xal',
        'xho',
        'xmf',
        'xnr',
        'xog',
        'yao',
        'yap',
        'yav',
        'ybb',
        'yid',
        'yor',
        'yrl',
        'yue',
        'zap',
        'zbl',
        'zea',
        'zen',
        'zgh',
        'zha',
        'zho',
        'zul',
        'zun',
        'zza',
    ];

    private const ALPHA2_TO_ALPHA3 = [
        'aa' => 'aar',
        'ab' => 'abk',
        'af' => 'afr',
        'ak' => 'aka',
        'am' => 'amh',
        'ar' => 'ara',
        'an' => 'arg',
        'as' => 'asm',
        'av' => 'ava',
        'ae' => 'ave',
        'ay' => 'aym',
        'az' => 'aze',
        'ba' => 'bak',
        'bm' => 'bam',
        'be' => 'bel',
        'bn' => 'ben',
        'bi' => 'bis',
        'bo' => 'bod',
        'bs' => 'bos',
        'br' => 'bre',
        'bg' => 'bul',
        'ca' => 'cat',
        'cs' => 'ces',
        'ch' => 'cha',
        'ce' => 'che',
        'cu' => 'chu',
        'cv' => 'chv',
        'kw' => 'cor',
        'co' => 'cos',
        'cr' => 'cre',
        'cy' => 'cym',
        'da' => 'dan',
        'de' => 'deu',
        'dv' => 'div',
        'dz' => 'dzo',
        'el' => 'ell',
        'en' => 'eng',
        'eo' => 'epo',
        'et' => 'est',
        'eu' => 'eus',
        'ee' => 'ewe',
        'fo' => 'fao',
        'fa' => 'fas',
        'fj' => 'fij',
        'fi' => 'fin',
        'fr' => 'fra',
        'fy' => 'fry',
        'ff' => 'ful',
        'gd' => 'gla',
        'ga' => 'gle',
        'gl' => 'glg',
        'gv' => 'glv',
        'gn' => 'grn',
        'gu' => 'guj',
        'ht' => 'hat',
        'ha' => 'hau',
        'he' => 'heb',
        'hz' => 'her',
        'hi' => 'hin',
        'ho' => 'hmo',
        'hr' => 'hrv',
        'hu' => 'hun',
        'hy' => 'hye',
        'ig' => 'ibo',
        'io' => 'ido',
        'ii' => 'iii',
        'iu' => 'iku',
        'ie' => 'ile',
        'ia' => 'ina',
        'id' => 'ind',
        'ik' => 'ipk',
        'is' => 'isl',
        'it' => 'ita',
        'jv' => 'jav',
        'ja' => 'jpn',
        'kl' => 'kal',
        'kn' => 'kan',
        'ks' => 'kas',
        'ka' => 'kat',
        'kr' => 'kau',
        'kk' => 'kaz',
        'km' => 'khm',
        'ki' => 'kik',
        'rw' => 'kin',
        'ky' => 'kir',
        'kv' => 'kom',
        'kg' => 'kon',
        'ko' => 'kor',
        'kj' => 'kua',
        'ku' => 'kur',
        'lo' => 'lao',
        'la' => 'lat',
        'lv' => 'lav',
        'li' => 'lim',
        'ln' => 'lin',
        'lt' => 'lit',
        'lb' => 'ltz',
        'lu' => 'lub',
        'lg' => 'lug',
        'mh' => 'mah',
        'ml' => 'mal',
        'mr' => 'mar',
        'mk' => 'mkd',
        'mg' => 'mlg',
        'mt' => 'mlt',
        'mn' => 'mon',
        'mi' => 'mri',
        'ms' => 'msa',
        'my' => 'mya',
        'na' => 'nau',
        'nv' => 'nav',
        'nr' => 'nbl',
        'nd' => 'nde',
        'ng' => 'ndo',
        'ne' => 'nep',
        'nl' => 'nld',
        'nn' => 'nno',
        'nb' => 'nob',
        'no' => 'nor',
        'ny' => 'nya',
        'oc' => 'oci',
        'oj' => 'oji',
        'or' => 'ori',
        'om' => 'orm',
        'os' => 'oss',
        'pa' => 'pan',
        'pi' => 'pli',
        'pl' => 'pol',
        'pt' => 'por',
        'ps' => 'pus',
        'qu' => 'que',
        'rm' => 'roh',
        'ro' => 'ron',
        'rn' => 'run',
        'ru' => 'rus',
        'sg' => 'sag',
        'sa' => 'san',
        'si' => 'sin',
        'sk' => 'slk',
        'sl' => 'slv',
        'se' => 'sme',
        'sm' => 'smo',
        'sn' => 'sna',
        'sd' => 'snd',
        'so' => 'som',
        'st' => 'sot',
        'es' => 'spa',
        'sq' => 'sqi',
        'sc' => 'srd',
        'sr' => 'srp',
        'ss' => 'ssw',
        'su' => 'sun',
        'sw' => 'swa',
        'sv' => 'swe',
        'ty' => 'tah',
        'ta' => 'tam',
        'tt' => 'tat',
        'te' => 'tel',
        'tg' => 'tgk',
        'th' => 'tha',
        'ti' => 'tir',
        'to' => 'ton',
        'tn' => 'tsn',
        'ts' => 'tso',
        'tk' => 'tuk',
        'tr' => 'tur',
        'ug' => 'uig',
        'uk' => 'ukr',
        'ur' => 'urd',
        'uz' => 'uzb',
        've' => 'ven',
        'vi' => 'vie',
        'vo' => 'vol',
        'wa' => 'wln',
        'wo' => 'wol',
        'xh' => 'xho',
        'yi' => 'yid',
        'yo' => 'yor',
        'za' => 'zha',
        'zh' => 'zho',
        'zu' => 'zul',
    ];

    private const ALPHA3_TO_ALPHA2 = [
        'aar' => 'aa',
        'abk' => 'ab',
        'ave' => 'ae',
        'afr' => 'af',
        'aka' => 'ak',
        'twi' => 'ak',
        'amh' => 'am',
        'arg' => 'an',
        'ara' => 'ar',
        'asm' => 'as',
        'ava' => 'av',
        'aym' => 'ay',
        'aze' => 'az',
        'bak' => 'ba',
        'bel' => 'be',
        'bul' => 'bg',
        'bis' => 'bi',
        'bam' => 'bm',
        'ben' => 'bn',
        'bod' => 'bo',
        'tib' => 'bo',
        'bre' => 'br',
        'bos' => 'bs',
        'cat' => 'ca',
        'che' => 'ce',
        'cha' => 'ch',
        'cos' => 'co',
        'cre' => 'cr',
        'ces' => 'cs',
        'cze' => 'cs',
        'chu' => 'cu',
        'chv' => 'cv',
        'cym' => 'cy',
        'wel' => 'cy',
        'dan' => 'da',
        'deu' => 'de',
        'ger' => 'de',
        'div' => 'dv',
        'dzo' => 'dz',
        'ewe' => 'ee',
        'ell' => 'el',
        'gre' => 'el',
        'eng' => 'en',
        'epo' => 'eo',
        'spa' => 'es',
        'est' => 'et',
        'baq' => 'eu',
        'eus' => 'eu',
        'fas' => 'fa',
        'per' => 'fa',
        'ful' => 'ff',
        'fin' => 'fi',
        'fij' => 'fj',
        'fao' => 'fo',
        'fra' => 'fr',
        'fre' => 'fr',
        'fry' => 'fy',
        'gle' => 'ga',
        'gla' => 'gd',
        'glg' => 'gl',
        'grn' => 'gn',
        'guj' => 'gu',
        'glv' => 'gv',
        'hau' => 'ha',
        'heb' => 'he',
        'hin' => 'hi',
        'hmo' => 'ho',
        'hrv' => 'hr',
        'hat' => 'ht',
        'hun' => 'hu',
        'arm' => 'hy',
        'hye' => 'hy',
        'her' => 'hz',
        'ina' => 'ia',
        'ind' => 'id',
        'ile' => 'ie',
        'ibo' => 'ig',
        'iii' => 'ii',
        'ipk' => 'ik',
        'ido' => 'io',
        'ice' => 'is',
        'isl' => 'is',
        'ita' => 'it',
        'iku' => 'iu',
        'jpn' => 'ja',
        'jav' => 'jv',
        'geo' => 'ka',
        'kat' => 'ka',
        'kon' => 'kg',
        'kik' => 'ki',
        'kua' => 'kj',
        'kaz' => 'kk',
        'kal' => 'kl',
        'khm' => 'km',
        'kan' => 'kn',
        'kor' => 'ko',
        'kau' => 'kr',
        'kas' => 'ks',
        'kur' => 'ku',
        'kom' => 'kv',
        'cor' => 'kw',
        'kir' => 'ky',
        'lat' => 'la',
        'ltz' => 'lb',
        'lug' => 'lg',
        'lim' => 'li',
        'lin' => 'ln',
        'lao' => 'lo',
        'lit' => 'lt',
        'lub' => 'lu',
        'lav' => 'lv',
        'mlg' => 'mg',
        'mah' => 'mh',
        'mao' => 'mi',
        'mri' => 'mi',
        'mac' => 'mk',
        'mkd' => 'mk',
        'mal' => 'ml',
        'mon' => 'mn',
        'mar' => 'mr',
        'may' => 'ms',
        'msa' => 'ms',
        'mlt' => 'mt',
        'bur' => 'my',
        'mya' => 'my',
        'nau' => 'na',
        'nob' => 'nb',
        'nde' => 'nd',
        'nep' => 'ne',
        'ndo' => 'ng',
        'dut' => 'nl',
        'nld' => 'nl',
        'nno' => 'nn',
        'nor' => 'no',
        'nbl' => 'nr',
        'nav' => 'nv',
        'nya' => 'ny',
        'oci' => 'oc',
        'oji' => 'oj',
        'orm' => 'om',
        'ori' => 'or',
        'oss' => 'os',
        'pan' => 'pa',
        'pli' => 'pi',
        'pol' => 'pl',
        'pus' => 'ps',
        'por' => 'pt',
        'que' => 'qu',
        'roh' => 'rm',
        'run' => 'rn',
        'mol' => 'ro',
        'ron' => 'ro',
        'rum' => 'ro',
        'rus' => 'ru',
        'kin' => 'rw',
        'san' => 'sa',
        'srd' => 'sc',
        'snd' => 'sd',
        'sme' => 'se',
        'sag' => 'sg',
        'sin' => 'si',
        'slk' => 'sk',
        'slo' => 'sk',
        'slv' => 'sl',
        'smo' => 'sm',
        'sna' => 'sn',
        'som' => 'so',
        'alb' => 'sq',
        'sqi' => 'sq',
        'srp' => 'sr',
        'ssw' => 'ss',
        'sot' => 'st',
        'sun' => 'su',
        'swe' => 'sv',
        'swa' => 'sw',
        'tam' => 'ta',
        'tel' => 'te',
        'tgk' => 'tg',
        'tha' => 'th',
        'tir' => 'ti',
        'tuk' => 'tk',
        'tsn' => 'tn',
        'ton' => 'to',
        'tur' => 'tr',
        'tso' => 'ts',
        'tat' => 'tt',
        'tah' => 'ty',
        'uig' => 'ug',
        'ukr' => 'uk',
        'urd' => 'ur',
        'uzb' => 'uz',
        'ven' => 've',
        'vie' => 'vi',
        'vol' => 'vo',
        'wln' => 'wa',
        'wol' => 'wo',
        'xho' => 'xh',
        'yid' => 'yi',
        'yor' => 'yo',
        'zha' => 'za',
        'chi' => 'zh',
        'zho' => 'zh',
        'zul' => 'zu',
    ];

    public function testGetLanguageCodes()
    {
        $this->assertEquals(self::LANGUAGES, Languages::getLanguageCodes());
    }

    /**
     * @dataProvider provideLocales
     */
    public function testGetNames($displayLocale)
    {
        if ('en' !== $displayLocale) {
            IntlTestHelper::requireFullIntl($this);
        }

        $languages = array_keys($names = Languages::getNames($displayLocale));

        sort($languages);

        $this->assertNotEmpty($languages);
        $this->assertSame([], array_diff($languages, self::LANGUAGES));

        foreach (Languages::getAlpha3Names($displayLocale) as $alpha3Code => $name) {
            $alpha2Code = self::ALPHA3_TO_ALPHA2[$alpha3Code] ?? null;
            if (null !== $alpha2Code) {
                $this->assertSame($name, $names[$alpha2Code]);
            }
        }
    }

    public function testGetNamesDefaultLocale()
    {
        IntlTestHelper::requireFullIntl($this);

        \Locale::setDefault('de_AT');

        $this->assertSame(Languages::getNames('de_AT'), Languages::getNames());
    }

    /**
     * @dataProvider provideLocaleAliases
     */
    public function testGetNamesSupportsAliases($alias, $ofLocale)
    {
        if ('en' !== $ofLocale) {
            IntlTestHelper::requireFullIntl($this);
        }

        // Can't use assertSame(), because some aliases contain scripts with
        // different collation (=order of output) than their aliased locale
        // e.g. sr_Latn_ME => sr_ME
        $this->assertEquals(Languages::getNames($ofLocale), Languages::getNames($alias));
    }

    /**
     * @dataProvider provideLocales
     */
    public function testGetName($displayLocale)
    {
        if ('en' !== $displayLocale) {
            IntlTestHelper::requireFullIntl($this);
        }

        $names = Languages::getNames($displayLocale);

        foreach ($names as $language => $name) {
            $this->assertSame($name, Languages::getName($language, $displayLocale));
        }
    }

    public function testLocalizedGetName()
    {
        $this->assertSame('Australian English', Languages::getName('en_AU', 'en'));
        $this->assertSame('Australian English', Languages::getName('en_AU_Zzzz', 'en'));
        $this->assertSame('English', Languages::getName('en_ZZ', 'en'));
    }

    public function testGetNameDefaultLocale()
    {
        IntlTestHelper::requireFullIntl($this);

        \Locale::setDefault('de_AT');

        $names = Languages::getNames('de_AT');

        foreach ($names as $language => $name) {
            $this->assertSame($name, Languages::getName($language));
        }
    }

    public static function provideLanguagesWithAlpha3Equivalent()
    {
        return array_map(
            fn ($value) => [$value],
            array_keys(self::ALPHA2_TO_ALPHA3)
        );
    }

    /**
     * @dataProvider provideLanguagesWithAlpha3Equivalent
     */
    public function testGetAlpha3Code($language)
    {
        $this->assertSame(self::ALPHA2_TO_ALPHA3[$language], Languages::getAlpha3Code($language));
    }

    public static function provideLanguagesWithoutAlpha3Equivalent()
    {
        return array_map(
            fn ($value) => [$value],
            array_diff(self::LANGUAGES, array_keys(self::ALPHA2_TO_ALPHA3))
        );
    }

    /**
     * @dataProvider provideLanguagesWithoutAlpha3Equivalent
     */
    public function testGetAlpha3CodeFailsIfNoAlpha3Equivalent($language)
    {
        $this->expectException(MissingResourceException::class);
        Languages::getAlpha3Code($language);
    }

    public function testGetNameWithInvalidLanguageCode()
    {
        $this->expectException(MissingResourceException::class);
        Languages::getName('foo');
    }

    public function testExists()
    {
        $this->assertTrue(Languages::exists('nl'));
        $this->assertFalse(Languages::exists('zxx'));
    }

    public function testGetAlpha3Codes()
    {
        $this->assertSame(self::ALPHA3_CODES, Languages::getAlpha3Codes());
    }

    public static function provideLanguagesWithAlpha2Equivalent()
    {
        return array_map(
            fn ($value) => [$value],
            array_keys(self::ALPHA3_TO_ALPHA2)
        );
    }

    /**
     * @dataProvider provideLanguagesWithAlpha2Equivalent
     */
    public function testGetAlpha2Code($language)
    {
        $this->assertSame(self::ALPHA3_TO_ALPHA2[$language], Languages::getAlpha2Code($language));
    }

    public static function provideLanguagesWithoutAlpha2Equivalent()
    {
        return array_map(
            fn ($value) => [$value],
            array_diff(self::ALPHA3_CODES, array_keys(self::ALPHA3_TO_ALPHA2))
        );
    }

    /**
     * @dataProvider provideLanguagesWithoutAlpha2Equivalent
     */
    public function testGetAlpha2CodeFailsIfNoAlpha2Equivalent($language)
    {
        $this->expectException(MissingResourceException::class);
        Languages::getAlpha2Code($language);
    }

    public function testAlpha3CodeExists()
    {
        $this->assertTrue(Languages::alpha3CodeExists('nob'));
        $this->assertTrue(Languages::alpha3CodeExists('nld'));
        $this->assertTrue(Languages::alpha3CodeExists('ace'));
        $this->assertTrue(Languages::alpha3CodeExists('nor'));
        $this->assertTrue(Languages::alpha3CodeExists('twi'));
        $this->assertTrue(Languages::alpha3CodeExists('tgl'));
        $this->assertFalse(Languages::alpha3CodeExists('en'));
        $this->assertFalse(Languages::alpha3CodeExists('foo'));
        $this->assertFalse(Languages::alpha3CodeExists('zzz'));
    }

    /**
     * @dataProvider provideLocales
     */
    public function testGetAlpha3Name($displayLocale)
    {
        if ('en' !== $displayLocale) {
            IntlTestHelper::requireFullIntl($this);
        }

        $names = Languages::getAlpha3Names($displayLocale);

        foreach ($names as $language => $name) {
            $this->assertSame($name, Languages::getAlpha3Name($language, $displayLocale));
        }
    }

    public function testGetAlpha3NameWithInvalidLanguageCode()
    {
        $this->expectException(MissingResourceException::class);

        Languages::getAlpha3Name('zzz');
    }

    /**
     * @dataProvider provideLocales
     */
    public function testGetAlpha3Names($displayLocale)
    {
        if ('en' !== $displayLocale) {
            IntlTestHelper::requireFullIntl($this);
        }

        $languages = array_keys($names = Languages::getAlpha3Names($displayLocale));

        sort($languages);

        $this->assertNotEmpty($languages);
        $this->assertSame([], array_diff($languages, self::ALPHA3_CODES));

        foreach (Languages::getNames($displayLocale) as $alpha2Code => $name) {
            $alpha3Code = self::ALPHA2_TO_ALPHA3[$alpha2Code] ?? (3 === \strlen($alpha2Code) ? $alpha2Code : null);
            if (null !== $alpha3Code) {
                $this->assertSame($name, $names[$alpha3Code]);
            }
        }
    }
}
