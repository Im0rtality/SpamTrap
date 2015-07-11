group { 'puppet': ensure => present }
Exec { path => [ '/bin/', '/sbin/', '/usr/bin/', '/usr/sbin/' ] }
File { owner => 0, group => 0, mode => 0644 }

user { "vagrant":
    ensure => present,
    groups => ["vagrant", "www-data"],
}

class {'apt':
    always_apt_update => true,
}

ensure_packages( [
    'augeas-tools',
    'vim',
    'curl',
    'git-core'
] )

apt::source { 'packages.dotdeb.org':
    location          => 'http://packages.dotdeb.org',
    release           => $lsbdistcodename,
    repos             => 'all',
    required_packages => 'debian-keyring debian-archive-keyring',
    key               => '89DF5277',
    key_server        => 'keys.gnupg.net',
    include_src       => true
}

apt::source { 'php56':
    location          => 'http://packages.dotdeb.org',
    release           => "${lsbdistcodename}-php56",
    repos             => 'all',
    key               => '89DF5277',
    key_server        => 'keys.gnupg.net',
    include_src       => true
}

class { 'php':
    version             => latest,
    package             => 'php5',
    service_autorestart => false,
    module_prefix       => ''
}

php::module {
    [
        'php5-cli',
        'php5-curl',
        'php5-common'
    ]:
        require => Class['php'],
}


class { 'composer':
    require => Package['php5', 'curl'],
}

class { 'elasticsearch':
    manage_repo  => true,
    repo_version => '1.6',
    java_install => true,
    config  => {
        'script.disable_dynamic' => false,
        'marvel.agent.enabled'   => false
    }
}

elasticsearch::plugin { 'mobz/elasticsearch-head':
    module_dir => 'head'
}

elasticsearch::plugin { 'elasticsearch/marvel/latest':
    module_dir => 'marvel'
}

elasticsearch::plugin { 'analysis-icu':
    module_dir => 'analysis-icu',
    ensure     => 'absent',
    before     => Elasticsearch::Plugin['elasticsearch/elasticsearch-analysis-icu/2.5.0']
}

elasticsearch::plugin { 'elasticsearch/elasticsearch-analysis-icu/2.5.0':
    module_dir => 'analysis-icu'
}
