const { execShellAsync } = require('../../../libs/common')
const { saveServerLog } = require('../../../libs/logging')

module.exports = async function (fastify) {
  fastify.get('/', async (request, reply) => {
    reply.code(200).send('I\'m trying, okay?')
    const upgradeP1 = await execShellAsync('bash ./install.sh upgrade')
    await saveServerLog({ event: upgradeP1, type: 'UPGRADE' })
  })
}
