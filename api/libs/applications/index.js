const dayjs = require('dayjs')

const { saveServerLog } = require('../logging')
const { execShellAsync, cleanupTmp } = require('../common');

const cloneRepository = require("./github/cloneRepository");
const { saveAppLog } = require('../logging')
const copyFiles = require("./deploy/copyFiles");
const buildContainer = require("./build/container");
const deploy = require("./deploy/deploy");
const Deployment = require('../../models/Deployment');
const { cleanup } = require('./cleanup')
const { saveConfiguration } = require('./configuration')

async function queueAndBuild(configuration, services, configChanged, imageChanged) {
    const { id, organization, name, branch } = configuration.repository
    const { domain } = configuration.publish
    const { deployId, nickname, workdir } = configuration.general
    try {
        await new Deployment({
            repoId: id, branch, deployId, domain, organization, name, nickname
        }).save()
        await saveAppLog(`${dayjs().format('YYYY-MM-DD HH:mm:ss.SSS')} Queued.`, configuration)
        await copyFiles(configuration);
        await buildContainer(configuration);
        await deploy(configuration, configChanged, imageChanged);
        await Deployment.findOneAndUpdate(
            { repoId: id, branch, deployId, organization, name, domain },
            { repoId: id, branch, deployId, organization, name, domain, progress: 'done' })
        // await saveConfiguration(configuration, services)
        cleanupTmp(workdir)

    } catch (error) {
        await cleanup(configuration);
        cleanupTmp(workdir)
        const { type } = error.error
        if (type === 'app') {
            await saveAppLog(error.error, configuration, true)
        } else {
            await saveServerLog(error.error, configuration)
        }
    }
}

module.exports = { queueAndBuild }
