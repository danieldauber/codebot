<template>
    <div>
        <h3>menu: {{ menu.locale }}</h3>

        <p>
            <router-link class="btn" :to="{path: '/menus'}">voltar</router-link>
            <a @click.prevent="sendToFacebook()" class="btn green">sicronizar</a>
            <a @click.prevent="remove()" class="btn red">remover</a>
        </p>

        <div v-if="menu.menu_buttons.length > 0">
            <div
                v-bind:key="menu_buttons.id"
                v-for="menu_buttons in menu.menu_buttons"
                v-if="menu_buttons.parent_id == 0"
            >
                <div class="waves-effect waves-light btn-large grey" style="margin-bottom: 15px;">
                    {{ menu_buttons.title }}
                    <small>{{ menu_buttons.type | menus_types }} {{ menu_buttons.value }}</small>

                    <div
                        class="waves-effect waves-light child btn-large child grey"
                        v-bind:key="child_menu_buttons.id"
                        v-for="child_menu_buttons in menu.menu_buttons"
                        v-if="child_menu_buttons.parent_id === menu_buttons.id"
                    >
                        {{ child_menu_buttons.title }}
                        <small>{{ child_menu_buttons.type | menus_types }} {{ child_menu_buttons.value }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card red" v-if="menu.menu_buttons.length == 0">
            <div class="card-content white-text">Nenhum botao</div>
        </div>

        <form @submit.prevent="create()" class="grey lighten-5" style="padding: 20px;">
            <strong>Novo Botão:</strong>
            <div class="input-field inline">
                <select required name v-model="newButton.type" class="browser-default">
                    <option value disabled>Tipo do Botão</option>
                    <option value="nested">Aninhado</option>
                    <option value="postback">Postback</option>
                    <option value="web_url">Link</option>
                </select>
            </div>
            <div class="input-field inline">
                <input type="text" v-model="newButton.title" required maxlength="20" />
                <label class="active">Título</label>
            </div>
            <div class="input-field inline" v-if="newButton.type === 'postback'">
                <input type="text" v-model="newButton.value" required maxlength="150" />
                <label class="active">Postback</label>
            </div>
            <div class="input-field inline" v-if="newButton.type === 'web_url'">
                <input type="text" v-model="newButton.value" required maxlength="150" />
                <label class="active">LInk</label>
            </div>

            <div class="input-field inline">
                <select name id class="browser-default" v-model="newButton.parent_id">
                    <option value disabled>Botão pai</option>
                    <option value="0">Nenhum</option>
                    <option
                        v-bind:key="menu_button.id"
                        v-for="menu_button in menu.menu_buttons"
                        :value="menu_button.id"
                    >{{menu_button.title}}</option>
                </select>
            </div>
            <button type="submit" class="btn">+</button>
        </form>
    </div>
</template>

<script>
import swal from 'sweetalert';
export default {
    data: function() {
        return {
            newButton: {
                type: "",
                parent_id: ""
            }
        }
    },
    methods: {
        create() {
            let data = {
                type: this.newButton.type,
                title: this.newButton.title,
                menu_id: this.$route.params.id,
                parent_id: this.newButton.parent_id
            }

            if(this.newButton.value) {
                data.value = this.newButton.value
            }

            if(!data.parent_id) {
                data.parent_id = 0
            }

            
            this.$store.dispatch('newMenuButton', data).then(() => {
                let params = {
                    id: this.$route.params.id,
                    data: {
                        facebook_diff: true
                    }
                   
                }
                this.$store.dispatch('updateMenu', params).then(() => {
                    this.$store.dispatch('getMenu', this.$route.params.id)
                })
            })
        },
        remove() {
                let item = this;
                swal({
                    title: "Removendo",
                    text: "Você está removendo este menu",
                    icon: "warning",
                    dangerMode: true,
                    buttons: true,
                }).then(function(isConfirm) {
                if (isConfirm) {
                    item.removeItens()
                    } 
                })
            },
            removeItens() {
                this.$store.dispatch('removeMenu', this.$route.params.id)
                    .then(() => {
                        swal("Removido", "Removido com sucesso", "success")
                        this.$router.push("/menus")
                    })

            },
            sendToFacebook() {
                this.$store.dispatch('sendToFacebook', this.$route.params.id).then(() => {
                    swal("Sincronizado", "Sincronizado com facebook", "success")
                })

            }
    },
    filters: {
        menus_types: function(value) {
            if(value === 'nested') {
                return 'Aninhado'
            }
            if(value === 'postback') {
                return 'Postback'
            }
            if(value === 'web_url') {
                return 'Link'
            }
        }
    },
    computed: {
        menu() {
            let menu = this.$store.state.menu.menu;
            return menu.menu_buttons ? menu : { menu_buttons: [] };
        }
    },
    mounted() {
        this.$store.dispatch("getMenu", this.$route.params.id);
    }
};
</script>

<style>
.child.btn-large {
    margin-left: 30px;
    
}
</style>